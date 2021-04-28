<?php

/**
 * Plugin Name:       Page Template Filter
 * Plugin URI:        https://minervawebdevelopment.com
 * Description:       Adds a filter on the pages UI to select a template. Also shows the current template in use as a new column labeled "Template".
 * Version:           1.0.0
 * Author:            Antonio Castiglione
 */

class PageTemplateFilter
{

	function init()
	{
		// Adding filter capability
		add_action('restrict_manage_posts', [$this, 'admin_pages_filter_dropdown']);
		add_filter('parse_query', [$this, 'admin_pages_filter']);

		// Showing current template column
		add_filter('manage_pages_columns', [$this, 'admin_pages_template_columns']);
		add_action('manage_pages_custom_column', [$this, 'admin_pages_template_column_content'], 20, 2);
	}

	# Dropdown to select the filter
	function admin_pages_filter_dropdown($post_type)
	{
		if ($post_type !== 'page') return;

		$selected_template = isset($_GET['template_filter']) ? $_GET['template_filter'] : "all";
?>
		<select name="template_filter">
			<option value="all">All Templates</option>

			<option value="default" <?php echo ($selected_template == 'default') ? ' selected="selected" ' : ""; ?>>
				<?php echo __('Default Template'); ?>
			</option>

			<?php page_template_dropdown($selected_template, 'page'); ?>
		</select>
<?php
	}

	# Filter to show specified template
	function admin_pages_filter($query)
	{
		if (is_admin() && isset($_GET['template_filter']) && $_GET['template_filter']) {

			if ($_GET['template_filter'] == 'all' || $_GET['template_filter'] == '') {
				return $query;
			}

			$query->query_vars['meta_key'] = '_wp_page_template';
			$query->query_vars['meta_value'] = esc_html($_GET['template_filter']);
		}
	}

	# Create the new column
	function admin_pages_template_columns($columns)
	{
		$columns['template'] = __('Template');
		return $columns;
	}

	# Display the current template
	function admin_pages_template_column_content($column, $post_id)
	{
		if ($column == 'template') {

			$template = get_post_meta($post_id, '_wp_page_template', true);

			if ($template == 'default') {
				echo "<span title='$template'>Default Template</span>";
			} else {
				$all_templates = get_page_templates($post_id, 'page');
				if (in_array($template, $all_templates)) {
					echo "<span title='$template'>" . array_search($template, $all_templates) . "</span>";
				} else {
					echo "<span>Template missing: $template</span>";
				}
			}

		}
	}
}

if (is_admin()) {
	$PageTemplateFilter = new PageTemplateFilter();
	$PageTemplateFilter->init();
}
