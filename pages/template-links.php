<?php
/*
Template Name: 友情链接
*/

function pk_get_wp_bookmarks($params = []) {
    // https://developer.wordpress.org/reference/functions/get_bookmarks/
    return get_bookmarks(array(
        'orderby' => isset($params['orderby']) ? $params['orderby'] : 'rating',
        'order' => isset($params['order']) ? $params['order'] : 'DESC',
        'limit' => isset($params['limit']) ? intval($params['limit']) ?? -1 : -1,
        'category' => isset($params['category']) ? $params['category'] : '',
        'category_name' => '',
        'hide_invisible' => 1,
        'show_updated' => isset($params['show_updated']) ? 1 : 0,
        'include' => isset($params['include']) ? $params['include'] : '',
        'exclude' => isset($params['exclude']) ? $params['exclude'] : '',
        'search' => isset($params['search']) ? $params['search'] : '',
    ));
}

// 支持 json api 按分类获取友情链接
if (isset($_REQUEST['json'])) {
    $bookmarks = pk_get_wp_bookmarks(array(
        'orderby' => isset($_REQUEST['orderby']) ? $_REQUEST['orderby'] : 'rating',
        'order' => isset($_REQUEST['order']) ? $_REQUEST['order'] : 'DESC',
        'limit' => isset($_REQUEST['limit']) ? intval($_REQUEST['limit']) ?? -1 : -1,
        'category' => isset($_REQUEST['cate']) ? $_REQUEST['cate'] : '',
        'show_updated' => 1,
        'include' => isset($_REQUEST['include']) ? $_REQUEST['include'] : '',
        'exclude' => isset($_REQUEST['exclude']) ? $_REQUEST['exclude'] : '',
        'search' => isset($_REQUEST['search']) ? $_REQUEST['search'] : '',
    ));

    header('Content-Type:application/json; charset=utf-8');
    isset($_GET['cors']) && header("Access-Control-Allow-Origin:*");
    isset($_REQUEST['nocache']) && header('cache-control: no-cache');

    die(json_encode($bookmarks));
}

$cats = get_post_meta($post->ID,'page_links_id',true);
$use_theme_link_forward = get_post_meta($post->ID,'use_theme_link_forward',true);

$links = pk_get_wp_bookmarks([ 'category' => $cats ]);

$groups = array();

if(!empty($cats) && $links && count($links)>0){
    $link_cats = get_all_category_id_row('link_category');
    $categorys = array();
    foreach ($link_cats as $cat) {
        $categories[$cat['value']] = $cat['label'];
    }

    foreach ($links as $link){
        if(!array_key_exists($link->term_id,$groups)){
            $groups[$link->term_id] = array(
                    'id'=>$link->term_id,
                    'name'=>$link->name ?? $categories[$link->term_id] ?? '',
                    'links'=>array()
            );
        }
        $groups[$link->term_id]['links'][] = $link;
    }
}

get_header();

?>

<div id="page" class="container mt20">
    <?php get_template_part('templates/box', 'global-top') ?>
    <?php echo pk_breadcrumbs(); while (have_posts()):the_post();?>
        <?php
         ?>
        <div id="page-links">
            <div id="page-<?php the_ID() ?>" class="row row-cols-1">
                <div id="posts" class="col-12 <?php pk_open_box_animated('animated fadeInLeft') ?> ">
                    <?php if(!empty(get_the_content())): ?>
                        <div class="mt20 p-block puock-text <?php get_entry_content_class() ?>">
                            <?php the_content() ?>
                        </div>
                    <?php endif; ?>
                    <div class="puock-text no-style">
                        <?php foreach ($groups as $group): ?>
                        <div class="p-block links-main" id="page-links-<?php echo $group['id'] ?>">
                            <h3><?php echo $group['name'] ?></h3>
                            <div class="links-main-box row t-sm">
                                <?php foreach ($group['links'] as $link): ?>
                                <?php $desc = $link->link_description ?? $link->link_notes ?? ''; ?>
                                <a class="link-item a-link col-lg-3 col-md-4 col-sm-6 col-6" href="<?php echo $use_theme_link_forward ? pk_go_link($link->link_url,$link->link_name) : $link->link_url; ?>" target="<?php echo $link->link_target ?>"
                                    rel="<?php echo $link->link_rel ?>" title="<?php echo $desc ?? $link->link_name ?>"
                                   data-bs-toggle="tooltip">
                                    <div class="clearfix puock-bg">
                                        <?php if (empty($link->link_image)) : ?>
                                            <img alt="<?php echo $link->link_name ?>" <?php echo pk_get_lazy_img_info(pk_get_favicon_url($link->link_url),'md-avatar') ?> alt="<?php echo $link->link_name ?>">
                                        <?php else :?>
                                            <img src="<?php echo $link->link_image ;?>"  alt="<?php echo $link->link_name ;?>" class="md-avatar" />
                                        <?php endif;?>
                                        <div class="info">
                                            <p class="ml-1 text-nowrap text-truncate"><?php echo $link->link_name ?></p>
                                            <p class="c-sub ml-1 text-nowrap text-truncate"><?php echo $desc ?? '暂无介绍' ?></p>
                                        </div>
                                    </div>
                                </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php comments_template() ?>
                </div>
            </div>
        </div>
    <?php endwhile; ?>
    <?php get_template_part('templates/box', 'global-bottom') ?>
</div>

<?php get_footer() ?>
