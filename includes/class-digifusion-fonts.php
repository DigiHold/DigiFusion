<?php
/**
 * DigiFusion Fonts Handler - Fixed
 * 
 * Handles loading Google Fonts locally or via CDN for DigiFusion theme.
 *
 * @package DigiFusion
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * DigiFusion Fonts Class
 */
class DigiFusion_Fonts {

	/**
	 * Instance of the fonts class.
	 *
	 * @var DigiFusion_Fonts
	 */
	private static $instance;

	/**
	 * Fonts directory path.
	 *
	 * @var string
	 */
	private $fonts_dir;

	/**
	 * Fonts directory URL.
	 *
	 * @var string
	 */
	private $fonts_url;

	/**
	 * User agent for Google Fonts API requests.
	 *
	 * @var string
	 */
	private $user_agent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/88.0.4324.150 Safari/537.36';

	/**
	 * Get instance of the fonts class.
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		// Set up fonts directory paths
		$upload_dir = wp_upload_dir();
		$this->fonts_dir = trailingslashit( $upload_dir['basedir'] ) . 'digifusion/fonts';
		$this->fonts_url = trailingslashit( $upload_dir['baseurl'] ) . 'digifusion/fonts';

		$this->init_hooks();
	}

	/**
	 * Initialize hooks.
	 */
	private function init_hooks() {
		// Enqueue fonts on frontend
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_fonts' ), 1 );
		
		// Process fonts when customizer settings change
		add_action( 'customize_save_after', array( $this, 'process_fonts_on_save' ) );
		
		// Clean up on theme switch
		add_action( 'switch_theme', array( $this, 'cleanup_fonts' ) );
	}

	/**
	 * Initialize WordPress Filesystem API.
	 *
	 * @return bool True if filesystem is available, false otherwise.
	 */
	private function init_filesystem() {
		global $wp_filesystem;

		if ( ! $wp_filesystem ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
			
			// Initialize the filesystem
			$filesystem_method = get_filesystem_method();
			if ( 'direct' === $filesystem_method ) {
				WP_Filesystem();
			} else {
				// For non-direct methods, we need credentials
				$credentials = request_filesystem_credentials( '', $filesystem_method, false, false, array() );
				if ( false === $credentials ) {
					return false;
				}
				if ( ! WP_Filesystem( $credentials ) ) {
					return false;
				}
			}
		}

		return ! empty( $wp_filesystem );
	}

	/**
	 * Get collected fonts from typography settings.
	 *
	 * @return array Array of fonts and their weights.
	 */
	private function get_typography_fonts() {
		$fonts = array();
		
		// Typography settings to check
		$typography_settings = array(
			'digifusion_body_typo',
			'digifusion_headings1_typo',
			'digifusion_headings2_typo',
			'digifusion_headings3_typo',
			'digifusion_headings4_typo',
			'digifusion_headings5_typo',
			'digifusion_headings6_typo',
			'digifusion_menu_typo',
			'digifusion_footer_typo',
		);
		
		foreach ( $typography_settings as $setting ) {
			$typography_json = get_theme_mod( $setting, '' );
			
			if ( empty( $typography_json ) ) {
				continue;
			}
			
			$typography_data = $this->parse_typography_setting( $typography_json );
			
			if ( empty( $typography_data ) || ! is_array( $typography_data ) ) {
				continue;
			}
			
			// Check if font family is set and not empty
			if ( empty( $typography_data['fontFamily'] ) || $this->is_system_font( $typography_data['fontFamily'] ) ) {
				continue;
			}
			
			$font_family = trim( $typography_data['fontFamily'] );
			
			// Get font weight - check multiple possible keys
			$font_weight = '400'; // Default weight
			if ( ! empty( $typography_data['fontWeight'] ) ) {
				$font_weight = $typography_data['fontWeight'];
			} elseif ( ! empty( $typography_data['font-weight'] ) ) {
				$font_weight = $typography_data['font-weight'];
			}
			
			// Normalize font weight
			$font_weight = $this->normalize_font_weight( $font_weight );
			
			// Initialize font family if not exists
			if ( ! isset( $fonts[ $font_family ] ) ) {
				$fonts[ $font_family ] = array();
			}
			
			// Add weight if not already present
			if ( ! in_array( $font_weight, $fonts[ $font_family ], true ) ) {
				$fonts[ $font_family ][] = $font_weight;
			}
		}
		
		return $fonts;
	}

	/**
	 * Enqueue fonts for frontend.
	 */
	public function enqueue_fonts() {
		$fonts = $this->get_typography_fonts();
		
		if ( empty( $fonts ) ) {
			return;
		}
		
		$use_local_fonts = get_theme_mod( 'digifusion_typography_local_fonts', false );
		
		if ( $use_local_fonts ) {
			// Local fonts
			$upload_dir = wp_upload_dir();
			$css_file = $upload_dir['basedir'] . '/digifusion/digifusion-fonts.css';
			$css_url = $upload_dir['baseurl'] . '/digifusion/digifusion-fonts.css';
			
			if ( file_exists( $css_file ) ) {
				wp_enqueue_style(
					'digifusion-local-fonts',
					$css_url,
					array(),
					filemtime( $css_file )
				);
				
				// Add preload links for critical fonts
				add_action( 'wp_head', array( $this, 'add_font_preload_links' ), 1 );
			}
		} else {
			// Google CDN - Use API v1 format
			$font_families = array();
			
			foreach ( $fonts as $font_family => $weights ) {
				$encoded_family = str_replace( ' ', '+', $font_family );
				
				if ( ! empty( $weights ) ) {
					// Remove duplicates and sort weights
					$weights = array_unique( $weights, SORT_NUMERIC );
					sort( $weights, SORT_NUMERIC );
					$font_families[] = $encoded_family . ':' . implode( ',', $weights );
				} else {
					$font_families[] = $encoded_family;
				}
			}
			
			if ( ! empty( $font_families ) ) {
				$google_fonts_url = 'https://fonts.googleapis.com/css?family=' . implode( '|', $font_families ) . '&display=swap';
				
				wp_enqueue_style(
					'digifusion-google-fonts',
					$google_fonts_url,
					array(),
					DIGIFUSION_VERSION
				);
			}
		}
	}

	/**
	 * Parse typography setting value.
	 *
	 * @param string $typography_value JSON string of typography settings.
	 * @return array
	 */
	private function parse_typography_setting( $typography_value ) {
		if ( empty( $typography_value ) ) {
			return array();
		}
		
		$typography = json_decode( $typography_value, true );
		return is_array( $typography ) ? $typography : array();
	}

	/**
	 * Check if a font family is a system font.
	 *
	 * @param string $font_family Font family.
	 * @return bool True if system font, false otherwise.
	 */
	private function is_system_font( $font_family ) {
		if ( strpos( $font_family, ',' ) !== false ) {
			return true;
		}
		
		$system_fonts = array(
			'serif', 'sans-serif', 'monospace', 'cursive', 'fantasy', 'system-ui',
			'Arial', 'Helvetica', 'Times New Roman', 'Times', 'Courier New', 
			'Courier', 'Verdana', 'Georgia', 'Palatino', 'Garamond', 'Bookman', 
			'Tahoma', 'Trebuchet MS', 'Impact', 'Comic Sans MS',
		);
		
		return in_array( $font_family, $system_fonts, true );
	}

	/**
	 * Normalize font weight.
	 *
	 * @param string|int $weight Font weight.
	 * @return string Normalized font weight.
	 */
	private function normalize_font_weight( $weight ) {
		// Handle empty or null values
		if ( empty( $weight ) ) {
			return '400';
		}
		
		// Convert to string for processing
		$weight = (string) $weight;
		
		// Handle named weights
		$weight_map = array(
			'normal'      => '400',
			'bold'        => '700',
			'thin'        => '100',
			'extra-light' => '200',
			'extralight'  => '200',
			'light'       => '300',
			'regular'     => '400',
			'medium'      => '500',
			'semi-bold'   => '600',
			'semibold'    => '600',
			'extra-bold'  => '800',
			'extrabold'   => '800',
			'black'       => '900',
		);
		
		$weight_lower = strtolower( trim( $weight ) );
		if ( isset( $weight_map[ $weight_lower ] ) ) {
			return $weight_map[ $weight_lower ];
		}
		
		// Handle numeric weights
		if ( is_numeric( $weight ) ) {
			$numeric_weight = (int) $weight;
			// Ensure weight is within valid range (100-900)
			if ( $numeric_weight >= 100 && $numeric_weight <= 900 ) {
				return (string) $numeric_weight;
			}
		}
		
		// Default fallback
		return '400';
	}

	/**
	 * Process fonts when customizer settings are saved.
	 */
	public function process_fonts_on_save() {
		$use_local_fonts = get_theme_mod( 'digifusion_typography_local_fonts', false );
		$fonts = $this->get_typography_fonts();
		
		// Always clean up first
		$this->cleanup_fonts();
		
		// Only process if we have fonts and want local fonts
		if ( ! empty( $fonts ) && $use_local_fonts ) {
			$this->create_fonts_directory();
			$this->download_and_process_fonts( $fonts );
		}
	}

	/**
	 * Create fonts directory if it doesn't exist.
	 *
	 * @return bool True on success, false on failure.
	 */
	private function create_fonts_directory() {
		if ( ! file_exists( $this->fonts_dir ) ) {
			$result = wp_mkdir_p( $this->fonts_dir );
			
			return $result;
		}
		return true;
	}

	/**
	 * Download and process fonts locally.
	 *
	 * @param array $fonts Array of fonts to process.
	 */
	private function download_and_process_fonts( $fonts ) {
		$css_content = '';
		
		foreach ( $fonts as $font_family => $weights ) {
			sort( $weights );
			$font_css = $this->process_font_family( $font_family, $weights );
			
			if ( $font_css ) {
				$css_content .= $font_css;
			}
		}
		
		if ( ! empty( $css_content ) ) {
			$this->save_fonts_css( $css_content );
		}
	}

	/**
	 * Process a single font family and its weights.
	 *
	 * @param string $font_family Font family.
	 * @param array  $weights Array of font weights.
	 * @return string|false Generated CSS or false on failure.
	 */
	private function process_font_family( $font_family, $weights ) {
		// Use Google Fonts CSS API v1 for better compatibility (like DigiBlocks)
		$url_font_family = str_replace( ' ', '+', $font_family );
		$weights_str = implode( ',', $weights );
		
		// Use API v1 format like DigiBlocks
		$fonts_url = "https://fonts.googleapis.com/css?family={$url_font_family}:{$weights_str}&display=swap";
		
		// Get the CSS from Google Fonts
		$response = wp_remote_get(
			$fonts_url,
			array(
				'user-agent' => $this->user_agent,
				'timeout'    => 30,
			)
		);
		
		if ( is_wp_error( $response ) ) {
			return false;
		}
		
		$response_code = wp_remote_retrieve_response_code( $response );
		if ( $response_code !== 200 ) {
			return false;
		}
		
		$css = wp_remote_retrieve_body( $response );
		
		return $this->process_google_fonts_css( $css, $font_family );
	}

	/**
	 * Process Google Fonts CSS - download files and generate local CSS.
	 *
	 * @param string $css Google Fonts CSS.
	 * @param string $font_family Font family.
	 * @return string|false Generated CSS or false on failure.
	 */
	private function process_google_fonts_css( $css, $font_family ) {
		// Group font-face rules by weight and style to avoid duplicates
		$font_variants = array();
		
		// Extract font-face blocks
		preg_match_all( '/@font-face\s*{([^}]*)}/i', $css, $font_face_matches );
		
		if ( empty( $font_face_matches[1] ) ) {
			return false;
		}
		
		// First pass: gather all variants and formats, avoid duplicates
		foreach ( $font_face_matches[1] as $font_face ) {
			// Extract font-weight
			preg_match( '/font-weight:\s*([^;]*);/i', $font_face, $weight_match );
			$weight = ! empty( $weight_match[1] ) ? trim( $weight_match[1] ) : '400';
			
			// Extract font-style
			preg_match( '/font-style:\s*([^;]*);/i', $font_face, $style_match );
			$style = ! empty( $style_match[1] ) ? trim( $style_match[1] ) : 'normal';
			
			// Extract unicode-range if available
			preg_match( '/unicode-range:\s*([^;]*);/i', $font_face, $range_match );
			$unicode_range = ! empty( $range_match[1] ) ? trim( $range_match[1] ) : '';
			
			// Create a key for this variant
			$variant_key = $weight . '-' . $style;
			
			// Initialize variant if not exists
			if ( ! isset( $font_variants[ $variant_key ] ) ) {
				$font_variants[ $variant_key ] = array(
					'weight' => $weight,
					'style' => $style,
					'formats' => array(),
				);
			}
			
			// Extract src URLs
			preg_match( '/src:\s*([^;]*);/i', $font_face, $src_match );
			
			if ( empty( $src_match[1] ) ) {
				continue;
			}
			
			preg_match_all( '/url\([\'"]?([^\'"*)]+)[\'"]?\)\s*format\([\'"]([^\'"]+)[\'"]\)/i', $src_match[1], $url_format_matches, PREG_SET_ORDER );
			
			if ( empty( $url_format_matches ) ) {
				continue;
			}
			
			// Process each URL and format pair
			foreach ( $url_format_matches as $match ) {
				$url = $match[1];
				$format = $match[2];
				
				// Add to formats if it doesn't exist
				if ( ! isset( $font_variants[ $variant_key ]['formats'][ $format ] ) ) {
					$font_variants[ $variant_key ]['formats'][ $format ] = array(
						'url' => $url,
						'unicode_range' => $unicode_range,
					);
				}
				// If we already have this format but the current one is for Latin (smaller unicode range),
				// prefer that one as it's typically smaller and sufficient for most Western websites
				elseif ( stripos( $unicode_range, 'U+0' ) === 0 ) {
					$font_variants[ $variant_key ]['formats'][ $format ] = array(
						'url' => $url,
						'unicode_range' => $unicode_range,
					);
				}
			}
		}
		
		// Second pass: download fonts and generate CSS (one @font-face per unique variant)
		$local_css = '';
		
		foreach ( $font_variants as $variant_key => $variant ) {
			$sources = array();
			
			// Process each format - prioritize woff2 format for modern browsers
			if ( isset( $variant['formats']['woff2'] ) ) {
				$format = 'woff2';
				$format_data = $variant['formats'][ $format ];
				$url = $format_data['url'];
				
				// Generate a filename
				$file_name = $this->generate_font_filename( $font_family, $variant['weight'], $variant['style'], $format );
				$file_path = $this->fonts_dir . '/' . $file_name;
				
				// Only download if the file doesn't exist
				if ( ! file_exists( $file_path ) ) {
					$this->download_font_file( $url, $file_path );
				}
				
				// Add to sources if file exists
				if ( file_exists( $file_path ) && filesize( $file_path ) > 0 ) {
					$sources[] = array(
						'url' => $this->fonts_url . '/' . $file_name,
						'format' => $format,
					);
				}
			}
			
			// Fallback to woff if woff2 is not available
			if ( empty( $sources ) && isset( $variant['formats']['woff'] ) ) {
				$format = 'woff';
				$format_data = $variant['formats'][ $format ];
				$url = $format_data['url'];
				
				// Generate a filename
				$file_name = $this->generate_font_filename( $font_family, $variant['weight'], $variant['style'], $format );
				$file_path = $this->fonts_dir . '/' . $file_name;
				
				// Only download if the file doesn't exist
				if ( ! file_exists( $file_path ) ) {
					$this->download_font_file( $url, $file_path );
				}
				
				// Add to sources if file exists
				if ( file_exists( $file_path ) && filesize( $file_path ) > 0 ) {
					$sources[] = array(
						'url' => $this->fonts_url . '/' . $file_name,
						'format' => $format,
					);
				}
			}
			
			// Only create @font-face if we have sources
			if ( ! empty( $sources ) ) {
				$local_css .= "@font-face {\n";
				$local_css .= "  font-family: '" . esc_attr( $font_family ) . "';\n";
				$local_css .= "  font-weight: " . esc_attr( $variant['weight'] ) . ";\n";
				$local_css .= "  font-style: " . esc_attr( $variant['style'] ) . ";\n";
				$local_css .= "  font-display: swap;\n";
				$local_css .= "  src: ";
				
				$src_parts = array();
				foreach ( $sources as $source ) {
					$src_parts[] = "url('" . esc_url( $source['url'] ) . "') format('" . esc_attr( $source['format'] ) . "')";
				}
				
				$local_css .= implode( ",\n         ", $src_parts ) . ";\n";
				$local_css .= "}\n\n";
			}
		}
		
		return $local_css;
	}

	/**
	 * Generate a filename for a font file.
	 *
	 * @param string $font_family Font family.
	 * @param string $weight Font weight.
	 * @param string $style Font style.
	 * @param string $format Font format.
	 * @return string File name.
	 */
	private function generate_font_filename( $font_family, $weight, $style, $format ) {
		$extension = $format === 'woff2' ? 'woff2' : 'woff';
		
		return sanitize_file_name(
			str_replace( ' ', '-', strtolower( $font_family ) ) . '-' .
			$weight . '-' .
			$style . '-' .
			substr( md5( $font_family . $weight . $style . $format ), 0, 8 ) . '.' .
			$extension
		);
	}

	/**
	 * Download a font file using WordPress Filesystem API.
	 *
	 * @param string $url Font file URL.
	 * @param string $file_path Destination file path.
	 * @return bool True on success, false on failure.
	 */
	private function download_font_file( $url, $file_path ) {
		// Initialize filesystem
		if ( ! $this->init_filesystem() ) {
			return false;
		}
		
		global $wp_filesystem;
		
		$response = wp_remote_get(
			$url,
			array(
				'user-agent' => $this->user_agent,
				'timeout'    => 30,
			)
		);
		
		if ( is_wp_error( $response ) ) {
			return false;
		}
		
		if ( wp_remote_retrieve_response_code( $response ) !== 200 ) {
			return false;
		}
		
		$font_data = wp_remote_retrieve_body( $response );
		
		if ( empty( $font_data ) ) {
			return false;
		}
		
		// Use WordPress filesystem API only
		return $wp_filesystem->put_contents( $file_path, $font_data, FS_CHMOD_FILE );
	}

	/**
	 * Save fonts CSS file using WordPress Filesystem API.
	 *
	 * @param string $css CSS content.
	 * @return bool True on success, false on failure.
	 */
	private function save_fonts_css( $css ) {
		// Initialize filesystem
		if ( ! $this->init_filesystem() ) {
			return false;
		}
		
		global $wp_filesystem;
		
		$upload_dir = wp_upload_dir();
		$digifusion_dir = $upload_dir['basedir'] . '/digifusion/';
		
		if ( ! file_exists( $digifusion_dir ) ) {
			wp_mkdir_p( $digifusion_dir );
		}
		
		$css_file = $digifusion_dir . 'digifusion-fonts.css';
		
		$header = "/* DigiFusion Fonts - Generated on " . current_time( 'Y-m-d H:i:s' ) . " */\n";
		$css_content = $header . $css;
		
		// Use WordPress filesystem API only
		return $wp_filesystem->put_contents( $css_file, $css_content, FS_CHMOD_FILE );
	}

	/**
	 * Add preload links for local font files.
	 */
	public function add_font_preload_links() {
		$fonts = $this->get_typography_fonts();
		
		if ( empty( $fonts ) ) {
			return;
		}
		
		// Preload only the most critical fonts (body font)
		$count = 0;
		foreach ( $fonts as $font_family => $weights ) {
			if ( $count >= 1 ) { // Only preload the first font
				break;
			}
			
			$primary_weight = ! empty( $weights ) ? $weights[0] : '400';
			$file_name = $this->generate_font_filename( $font_family, $primary_weight, 'normal', 'woff2' );
			$font_url = $this->fonts_url . '/' . $file_name;
			
			if ( file_exists( $this->fonts_dir . '/' . $file_name ) ) {
				echo '<link rel="preload" href="' . esc_url( $font_url ) . '" as="font" type="font/woff2" crossorigin>' . "\n";
			}
			
			$count++;
		}
	}

	/**
	 * Cleanup fonts directory and files.
	 */
	public function cleanup_fonts() {
		$upload_dir = wp_upload_dir();
		$digifusion_dir = $upload_dir['basedir'] . '/digifusion/';
		$css_file = $digifusion_dir . 'digifusion-fonts.css';
		
		// Remove CSS file
		if ( file_exists( $css_file ) ) {
			wp_delete_file( $css_file );
		}
		
		// Remove font files
		if ( file_exists( $this->fonts_dir ) && is_dir( $this->fonts_dir ) ) {
			$files = glob( $this->fonts_dir . '/*' );
			if ( $files ) {
				foreach ( $files as $file ) {
					if ( is_file( $file ) ) {
						wp_delete_file( $file );
					}
				}
			}
			
			// Remove fonts directory if empty
			if ( $this->is_directory_empty( $this->fonts_dir ) ) {
				rmdir( $this->fonts_dir );
			}
		}
		
		// Remove main digifusion directory if empty
		if ( file_exists( $digifusion_dir ) && $this->is_directory_empty( $digifusion_dir ) ) {
			rmdir( $digifusion_dir );
		}
	}

	/**
	 * Check if directory is empty.
	 *
	 * @param string $dir Directory path.
	 * @return bool
	 */
	private function is_directory_empty( $dir ) {
		$handle = opendir( $dir );
		while ( false !== ( $entry = readdir( $handle ) ) ) {
			if ( $entry !== '.' && $entry !== '..' ) {
				closedir( $handle );
				return false;
			}
		}
		closedir( $handle );
		return true;
	}
}