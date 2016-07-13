<?php


function breadcrumbs($args=array()) {
    return new Atf_Breadcrumbs($args);
}

if (!class_exists('Atf_Breadcrumbs')) {
    class Atf_Breadcrumbs {
        public $args = array();
        public $return;
        public function __construct($args)
        {
            $args = wp_parse_args($args, array(
                'text_home'     => __('Home'), // text for the 'Home' link
                'text_category' => '%s', // text for a category page
                'text_search'   => 'Search Results for "%s" Query', // text for a search results page
                'text_tag'      => 'Posts Tagged "%s"', // text for a tag page
                'text_author'   => 'Articles Posted by %s', // text for an author page
                'text_404'      => 'Error 404', // text for the 404 page
                'show_current'  => 1, // 1 - show current post/page/category title in breadcrumbs, 0 - don't show
                'post_type_taxonomy' => array(
                    'post' => 'category',
                    'page' => '',
                    'product' => 'product_cat',
                ),
                'show_on_home'   => 0, // 1 - show breadcrumbs on the homepage, 0 - don't show
                'show_home_link' => 0, // 1 - show the 'Home' link, 0 - don't show
                'show_title'   => 1, // 1 - show the title for the links, 0 - don't show
                'show_post_type' => true,
                'delimiter'   => '  ', // delimiter between crumbs
                'before_current'   => '<li class="active">', // tag before the current crumb
                'after_current'     => '</li>', // tag after the current crumb
                'before_crumb' => '<li>',
                'after_crumb' => '</li>',
                'before' => '<ol class="breadcrumb" xmlns:v="http://rdf.data-vocabulary.org/#">',
                'after' => '</ol>',
            ));
            /* === OPTIONS === */


            /* === END OF OPTIONS === */

            global $wp_query, $post;
            $home_link    = home_url('/');
            $link_before  = '<span typeof="v:Breadcrumb">';
            $link_after   = '</span>';
            $link_attr    = ' rel="v:url" property="v:title"';
            $link         = $link_before . '<a' . $link_attr . ' href="%1$s">%2$s</a>' . $link_after;
            $parent_id    = 0;
            $frontpage_id = get_option('page_on_front');
            $post_types   = array_keys($args[ 'post_type_taxonomy']);

            if (is_home() || is_front_page()) {

                if ($args['show_on_home']) echo $args['before'] . $args['before_current'] . '<div class="breadcrumbs"><a href="' . $home_link . '">' . $args['text_home'] . '</a>' . $args['after_current'] . $args['after'];

            } else {

                echo $args['before'];

                if ($args ['show_home_link'] == 1) {
                    echo $args['before_crumb'] . '<a href="' . $home_link . '" rel="v:url" property="v:title">' . $args['text_home'] . '</a>' . $args['after_crumb'];
                    if ($frontpage_id == 0 || $parent_id != $frontpage_id) echo $args['delimiter'];
                }

                if ( is_tax() || is_category() ) {

                    $queried_obj = get_queried_object();

                    $this_cat = get_term($queried_obj->term_id, $queried_obj->taxonomy);

                    $cats = '';

                    if ($this_cat->parent != 0 && is_taxonomy_hierarchical($queried_obj->taxonomy)) {

                        $cats = $args['before_crumb'] . get_category_parents($this_cat->parent, TRUE, $args['after_crumb'] . $args['delimiter'] . $args['before_crumb']);

                        $cats = substr($cats, 0, (strlen($cats) - strlen($args['delimiter'] . $args['before_crumb'])));

                        if ($args['show_current'] == 0) $cats = preg_replace("#^(.+){$args['delimiter']}$#", "$1", $cats);

                        if (!$args['show_title']) $cats = preg_replace('/ title="(.*?)"/', '', $cats);

                    }

                    if ($args['show_current'] == 1) $cats .= $args['before_current'] . $this_cat->name . $args['after_current'];

                    echo $cats;

                } elseif ( is_search() ) {
                    echo sprintf($link, get_year_link(get_the_time('Y')), get_the_time('Y')) . $args['delimiter'];
                    echo $args['before_current'] . get_the_time('F') . $args['after_current'];

                } elseif ( is_year() ) {
                    echo $args['before_current'] . sprintf($args['text_search'], get_search_query()) . $args['after_current'];

                } elseif ( is_day() ) {
                    echo sprintf($link, get_year_link(get_the_time('Y')), get_the_time('Y')) . $args['delimiter'];
                    echo sprintf($link, get_month_link(get_the_time('Y'),get_the_time('m')), get_the_time('F')) . $args['delimiter'];
                    echo $args['before_current'] . get_the_time('d') . $args['after_current'];

                } elseif ( is_month() ) {
                    echo sprintf($link, get_year_link(get_the_time('Y')), get_the_time('Y')) . $args['delimiter'];
                    echo $args['before_current'] . get_the_time('F') . $args['after_current'];

                } elseif ( is_year() ) {
                    echo $args['before_current'] . get_the_time('Y') . $args['after_current'];

                } elseif ( is_single() && !is_attachment() ) {

                    $post_type = get_post_type();

                    if ( in_array($post_type, $post_types) ) {

                        $cat = get_the_terms( false, $args['post_type_taxonomy'][$post_type] );
                        $cat = $cat[0];
                        $cats = $args['before_crumb'] . get_category_parents($cat, TRUE, $args['after_crumb'] . $args['delimiter'] . $args['before_crumb']);
                        $cats = substr($cats, 0, (strlen($cats) - strlen($args['delimiter'] . $args['before_crumb'])));
                        echo $cats;
                        if ($args['show_current']) echo $args['before_current'] . get_the_title() . $args['after_current'];


                    } elseif ( $post_type != 'post' ) {

                        if ($args['show_post_type']) {
                            $post_type = get_post_type_object(get_post_type());
                            $slug = $post_type->rewrite;
                            echo $args['before_crumb'];
                            printf($link, $home_link . $slug['slug'] . '/', $post_type->labels->singular_name);
                            echo $args['after_crumb'];
                        }

                        if ($args['show_current']) echo $args['delimiter'] . $args['before_current'] . get_the_title() . $args['after_current'];
                    } else {
                        $cat = get_the_category(); $cat = $cat[0];
                        $cats = get_category_parents($cat, TRUE, $args['delimiter']);
                        if ($args['show_current']) $cats = preg_replace("#^(.+){$args['delimiter']}$#", "$1", $cats);
                        $cats = str_replace('<a', $link_before . '<a' . $link_attr, $cats);
                        $cats = str_replace('</a>', '</a>' . $link_after, $cats);
                        if ($args['show_title'] == 0) $cats = preg_replace('/ title="(.*?)"/', '', $cats);
                        echo $cats;
                        if ($args['show_current']) echo $args['before_current'] . get_the_title() . $args['after_current'];
                    }

                } elseif ( is_attachment() ) {
                    $parent = get_post($parent_id);
                    $cat = get_the_category($parent->ID); $cat = $cat[0];
                    if ($cat) {
                        $cats = get_category_parents($cat, TRUE, $args['delimiter']);
                        $cats = str_replace('<a', $link_before . '<a' . $link_attr, $cats);
                        $cats = str_replace('</a>', '</a>' . $link_after, $cats);
                        if ($args['show_title'] == 0) $cats = preg_replace('/ title="(.*?)"/', '', $cats);
                        echo $cats;
                    }
                    printf($link, get_permalink($parent), $parent->post_title);
                    if ($args['show_current']) echo $args['delimiter'] . $args['before_current'] . get_the_title() . $args['after_current'];

                } elseif ( is_page() && !$parent_id ) {
                    if ($args['show_current']) echo $args['before_current'] . get_the_title() . $args['after_current'];

                } elseif ( is_page() && $parent_id ) {
                    if ($parent_id != $frontpage_id) {
                        $breadcrumbs = array();
                        while ($parent_id) {
                            $page = get_page($parent_id);
                            if ($parent_id != $frontpage_id) {
                                $breadcrumbs[] = sprintf($link, get_permalink($page->ID), get_the_title($page->ID));
                            }
                            $parent_id = $page->post_parent;
                        }
                        $breadcrumbs = array_reverse($breadcrumbs);
                        for ($i = 0; $i < count($breadcrumbs); $i++) {
                            echo $breadcrumbs[$i];
                            if ($i != count($breadcrumbs)-1) echo $args['delimiter'];
                        }
                    }
                    if ($args['show_current']) {
                        if ($args['show_home_link'] == 1 || ($parent_id_2 != 0 && $parent_id_2 != $frontpage_id)) echo $args['delimiter'];
                        echo $args['before_current'] . get_the_title() . $args['after_current'];
                    }

                } elseif ( is_tag() ) {
                    echo $args['before_current'] . sprintf($args['text_tag'], single_tag_title('', false)) . $args['after_current'];


                } elseif ( is_author() ) {
                    global $author;
                    $userdata = get_userdata($author);
                    echo $args['before_current'] . sprintf($args['text_author'], $userdata->display_name) . $args['after_current'];

                } elseif ( is_404() ) {
                    echo $args['before_current'] . $args['text_404'] . $args['after_current'];

                } elseif ( has_post_format() && !is_singular() ) {
                    echo get_post_format_string( get_post_format() );

                }elseif ( !is_single() && !is_page() && get_post_type() != 'post' && !is_404() ) {
                    $post_type = get_post_type_object(get_post_type());
                    echo $args['before_current'] . $post_type->labels->singular_name . $args['after_current'];

                }



                if ( get_query_var('paged') ) {
                    if ( is_category() || is_day() || is_month() || is_year() || is_search() || is_tag() || is_author() ) echo ' (';
                    echo __('Page') . ' ' . get_query_var('paged');
                    if ( is_category() || is_day() || is_month() || is_year() || is_search() || is_tag() || is_author() ) echo ')';
                }

                echo $args['after'];

            }
        }
    }
}



