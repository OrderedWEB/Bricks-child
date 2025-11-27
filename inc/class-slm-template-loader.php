<?php
/**
 * SLM Template Loader
 * 
 * Registers and loads page templates from module subdirectories.
 * Enables templates in /inc/{module}/pages/ to appear in WP page template dropdown.
 *
 * @package Bricks_Child
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class SLM_Template_Loader {

    /**
     * Singleton instance
     *
     * @var SLM_Template_Loader
     */
    private static $instance = null;

    /**
     * Base path for includes
     *
     * @var string
     */
    private $inc_path;

    /**
     * Registered templates
     * Format: ['template-path.php' => 'Template Name']
     *
     * @var array
     */
    private $templates = [];

    /**
     * Module directories to scan for templates
     *
     * @var array
     */
    private $modules = [
        'portal',
        'task',
        'dms',
        'messaging',
        'engagement-letters',
    ];

    /**
     * Get singleton instance
     *
     * @return SLM_Template_Loader
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        $this->inc_path = get_stylesheet_directory() . '/inc';
        
        $this->discover_templates();
        $this->init_hooks();
    }

    /**
     * Initialize WordPress hooks
     */
    private function init_hooks() {
        // Add templates to page template dropdown
        add_filter('theme_page_templates', [$this, 'register_templates'], 10, 4);
        
        // Load template from our directory
        add_filter('template_include', [$this, 'load_template'], 99);
        
        // Ensure template is valid when saving page
        add_filter('wp_insert_post_data', [$this, 'validate_template_on_save'], 10, 2);
    }

    /**
     * Discover templates from all module directories
     */
    private function discover_templates() {
        foreach ($this->modules as $module) {
            $pages_dir = $this->inc_path . '/' . $module . '/pages';
            
            if (!is_dir($pages_dir)) {
                continue;
            }

            $files = glob($pages_dir . '/page-*.php');
            
            foreach ($files as $file) {
                $template_data = $this->get_template_data($file);
                
                if ($template_data) {
                    // Key format: module/pages/filename.php
                    $relative_path = $module . '/pages/' . basename($file);
                    $this->templates[$relative_path] = $template_data['name'];
                }
            }
        }
    }

    /**
     * Extract template name from file header
     *
     * @param string $file Full path to template file
     * @return array|false Template data or false if not a valid template
     */
    private function get_template_data($file) {
        if (!is_readable($file)) {
            return false;
        }

        // Read first 8KB of file
        $file_content = file_get_contents($file, false, null, 0, 8192);
        
        if (empty($file_content)) {
            return false;
        }

        // Look for Template Name header
        if (preg_match('/Template Name:\s*(.+)$/mi', $file_content, $matches)) {
            $template_name = trim($matches[1]);
            
            // Also try to get description
            $description = '';
            if (preg_match('/Description:\s*(.+)$/mi', $file_content, $desc_matches)) {
                $description = trim($desc_matches[1]);
            }

            return [
                'name' => $template_name,
                'description' => $description,
            ];
        }

        return false;
    }

    /**
     * Register templates with WordPress
     *
     * @param array $templates Existing templates
     * @param WP_Theme $theme Theme object
     * @param WP_Post|null $post Current post
     * @param string $post_type Post type
     * @return array Modified templates array
     */
    public function register_templates($templates, $theme, $post, $post_type) {
        // Only add to pages
        if ($post_type !== 'page') {
            return $templates;
        }

        return array_merge($templates, $this->templates);
    }

    /**
     * Load template from module directory
     *
     * @param string $template Default template path
     * @return string Template path to load
     */
    public function load_template($template) {
        global $post;

        if (!$post) {
            return $template;
        }

        $page_template = get_post_meta($post->ID, '_wp_page_template', true);

        // Check if it's one of our templates
        if (empty($page_template) || !isset($this->templates[$page_template])) {
            return $template;
        }

        // Build full path
        $custom_template = $this->inc_path . '/' . $page_template;

        if (file_exists($custom_template)) {
            return $custom_template;
        }

        return $template;
    }

    /**
     * Validate template exists when saving page
     *
     * @param array $data Post data
     * @param array $postarr Raw post data
     * @return array Modified post data
     */
    public function validate_template_on_save($data, $postarr) {
        if (!isset($postarr['page_template'])) {
            return $data;
        }

        $template = $postarr['page_template'];

        // If it's one of ours, verify it exists
        if (isset($this->templates[$template])) {
            $full_path = $this->inc_path . '/' . $template;
            
            if (!file_exists($full_path)) {
                // Log error but don't block save
                error_log(sprintf(
                    'SLM Template Loader: Template file not found: %s',
                    $full_path
                ));
            }
        }

        return $data;
    }

    /**
     * Get all registered templates
     *
     * @return array
     */
    public function get_templates() {
        return $this->templates;
    }

    /**
     * Check if a template is registered
     *
     * @param string $template_path Relative template path
     * @return bool
     */
    public function is_registered($template_path) {
        return isset($this->templates[$template_path]);
    }

    /**
     * Get full path for a template
     *
     * @param string $template_path Relative template path
     * @return string|false Full path or false if not found
     */
    public function get_template_path($template_path) {
        if (!$this->is_registered($template_path)) {
            return false;
        }

        $full_path = $this->inc_path . '/' . $template_path;
        
        return file_exists($full_path) ? $full_path : false;
    }

    /**
     * Manually register a template (for dynamically added templates)
     *
     * @param string $relative_path Path relative to /inc/
     * @param string $name Template display name
     * @return bool Success
     */
    public function register_template($relative_path, $name) {
        $full_path = $this->inc_path . '/' . $relative_path;
        
        if (!file_exists($full_path)) {
            return false;
        }

        $this->templates[$relative_path] = $name;
        return true;
    }
}

/**
 * Initialize the template loader
 *
 * @return SLM_Template_Loader
 */
function slm_template_loader() {
    return SLM_Template_Loader::get_instance();
}