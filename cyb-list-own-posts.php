<?php
/**
 * Plugin Name:  Cyb List Own Posts
 * Plugin URI:   https://github.com/CybMeta/cyb-list-own-posts
 * Description:  List only own posts in admin screen if user can not edit_others_posts
 * Version:      0.1
 * Author:       Juan Padial (@CybMeta)
 * Author URI:   http://cybmeta.com
 * License: GPLv3 or later
 */

add_action( 'load-edit.php', 'cyb_list_own_posts_for_authors' );
function cyb_list_own_posts_for_authors() {

    add_action( 'parse_request', function( $query ) {
 
    $post_type_object = get_post_type_object( $query->query_vars['post_type'] );
 
    if ( ! is_null( $post_type_object ) && ! current_user_can( $post_type_object->cap->edit_others_posts ) ) {
 
      $query->query_vars['author'] = get_current_user_id();

    }
 
  } );

}

add_filter( 'views_edit-post', 'cyb_views_filter_for_own_posts' );
add_filter( 'views_edit-news', 'cyb_views_filter_for_own_posts' );
function cyb_views_filter_for_own_posts( $views ) {
 
  $post_type = get_query_var( 'post_type' );
  $post_type_object = get_post_type_object( $post_type );
  
  // No seguir si el usuario puede editar los posts de otros autores
  if ( is_null( $post_type_object ) || current_user_can( $post_type_object->cap->edit_others_posts ) ) {

    return $views;

  }
  $author = get_current_user_id();

  unset($views['mine']);
 
  $new_views = array(
    'all' => __('All'),
    'publish' => __('Published'),
    'private' => __('Private'),
    'pending' => __('Pending Review'),
    'future' => __('Scheduled'),
    'draft' => __('Draft'),
    'trash' => __('Trash')
  );
 
  foreach( $new_views as $view => $name ) {
 
    $query = array(
      'author' => $author,
      'post_type' => $post_type
    );
 
    if($view == 'all') {
 
      $query['all_posts'] = 1;
      $class = ( get_query_var('all_posts') == 1 || get_query_var('post_status') == '' ) ? ' class="current"' : '';
      $url_query_var = 'all_posts=1';
 
    } else {
 
      $query['post_status'] = $view;
      $class = ( get_query_var('post_status') == $view ) ? ' class="current"' : '';
      $url_query_var = 'post_status='.$view;
 
    }
 
    $result = new WP_Query($query);
 
    if($result->found_posts > 0) {
 
      $views[$view] = sprintf( 
        '<a href="%s"'. $class .'>'.__($name).' <span class="count">(%d)</span></a>',
        admin_url('edit.php?'.$url_query_var.'&post_type='.$post_type),
        $result->found_posts
      );
 
    } else {
 
      unset($views[$view]);
 
    }
 
  }

  return $views;

}

add_filter( 'views_edit-post', 'cyb_remove_views_filter_counter' );
add_filter( 'views_edit-news', 'cyb_remove_views_filter_counter' );
function cyb_remove_views_filter_counter( $views ) {

  foreach ( $views as $index => $view ) {

    $views[ $index ] = preg_replace( '/ <span class="count">\([0-9]+\)<\/span>/', '', $view );

  }

  return $views;

}
