<?php
class MogRun_Action extends Typecho_Widget implements Widget_Interface_Do
{
  /**
   * 导出 JSON
   *
   * @access public
   * @return void
   */
  public function doExport($request)
  {

    // Get the database object
    $db = Typecho_Db::get();
    $prefix = $db->getPrefix();
    $comment_table = $prefix . 'comments';
    $content_table = $prefix . 'contents';
    $metas_table = $prefix . 'metas';
    $relationships_table = $prefix . 'relationships';
    $options_table = $prefix . 'options';
    $links_table = $prefix . 'links';
    $users_table = $prefix . 'users';

    $uid = $request->get('uid');

    // start to get data in database.
    $sql = "SELECT * FROM {$content_table} WHERE `type` in ('post','page')";
    $tpContent = $db->fetchAll($db->query($sql));

    //获取到所有的文章 
    $sql = "SELECT * FROM {$content_table} WHERE `type` in ('post')";
    $tpPost = $db->fetchAll($db->query($sql));

    //获取到所有的页面
    $sql = "SELECT * FROM {$content_table} WHERE `type` in ('page')";
    $tpPage = $db->fetchAll($db->query($sql));

    //获取文章所有的评论
    $sql = "SELECT * FROM {$content_table} INNER JOIN {$comment_table} ON {$content_table}.`cid` = {$comment_table}.`cid` WHERE ({$content_table}.`type` = 'post')";
    $tpPostComments = $db->fetchAll($db->query($sql));
    //获取页面所有的评论
    $sql = "SELECT * FROM {$content_table} INNER JOIN {$comment_table} ON {$content_table}.`cid` = {$comment_table}.`cid` WHERE ({$content_table}.`type` = 'page')";
    $tpPageComments = $db->fetchAll($db->query($sql));

    //获取到所有的分类
    $sql = "SELECT * FROM {$metas_table} WHERE `type` in ('category')";
    $tpCategory = $db->fetchAll($db->query($sql));

    $sql = "SELECT * FROM {$metas_table} INNER JOIN {$relationships_table} ON {$metas_table}.`mid` = {$relationships_table}.`mid` WHERE `type` in ('category')";
    $categorys = $db->fetchAll($db->query($sql));

    // 获取关系链
    $sql = "SELECT * FROM {$relationships_table}";
    $tpRelationships = $db->fetchAll($db->query($sql));

    //获取到所有的标签
    $sql = "SELECT * FROM {$metas_table} WHERE `type` in ('tag')";
    $tpTag = $db->fetchAll($db->query($sql));

    $sql = "SELECT * FROM {$metas_table} INNER JOIN {$relationships_table} ON {$metas_table}.`mid` = {$relationships_table}.`mid` WHERE `type` in ('tag')";
    $tpTags = $db->fetchAll($db->query($sql));

    //获取到所有的友情链接
    $sql = "SELECT * FROM {$links_table}";
    $tpLinks = $db->fetchAll($db->query($sql));

    // 获取指定管理用户
    $sql = "SELECT * FROM {$users_table} WHERE `uid` = {$uid}";
    $tpUser = $db->fetchAll($db->query($sql))[0];

    // 构造数据顺序：categories -> post & pages -> comments -> user / friends
    // post 依赖 category id，而在 构造 post 的时候，还需要绑定 tag，但是这部分可以在构造 post 的时候绑定。
    // comments 依赖 post id / page id
    // 其他的随意

    /**
     * 构造分类数据
     * name, slug, description
     */
    $categories = array();
    foreach ($tpCategory as $category) {
      $categories[] = array(
        'name' => $category['name'],
        'slug' => $category['slug'],
        'description' => $category['description'],
      );
    }

    /**
     * 构造文章数据
     * title, slug, created, modified, text, category_id, tags
     */
    $posts = array();
    foreach ($tpPost as $post) {
      $postTags = array();
      foreach ($tpTags as $tag) {
        if ($tag['cid'] == $post['cid']) {
          $postTags[] = $tag['name'];
        }
      }
      $post['created'] = date('c', $post['created']);
      $post['modified'] = date('c', $post['modified']);
      // 从 tpRelationships 遍历，找到对应的 cid 的 mid
      $mid = null;
      foreach ($tpRelationships as $relationship) {
        if ($relationship['cid'] == $post['cid']) {
          $mid = $relationship['mid'];
          break;
        }
      }
      // 从 tpCategory 中找到对应的 slug
      $post['category_id'] = null;
      foreach ($tpCategory as $category) {
        if ($category['mid'] == $mid) {
          $post['category_id'] = $category['slug'];
          break;
        }
      }
      $post['text'] = preg_replace('/<!--markdown-->/', '', $post['text'], 1);
      $posts[] = array(
        'title' => $post['title'],
        'slug' => $post['slug'],
        'created' => $post['created'],
        'modified' => $post['modified'],
        'text' => $post['text'],
        'category_id' => $post['category_id'],
        'tags' => $postTags,
      );
    }

    /**
     * 构造页面数据
     * title, subtitle, slug, created, modified, text, order
     */
    $pages = array();
    foreach ($tpPage as $page) {
      $page['created'] = date('c', $page['created']);
      $page['modified'] = date('c', $page['modified']);
      $page['text'] = preg_replace('/<!--markdown-->/', '', $page['text'], 1);
      $pages[] = array(
        'title' => $page['title'],
        'subtitle' => $page['subtitle'],
        'slug' => $page['slug'],
        'created' => $page['created'],
        'modified' => $page['modified'],
        'text' => $page['text'],
        'order' => $page['order'],
      );
    }

    /**
     * 构造评论数据
     * pid, parent, children[], text, author, email, url, status, created
     */
    $comments = array();
    foreach ($tpPostComments as $comment) {
      $children = array();
      foreach ($tpPostComments as $child) {
        if ($child['parent'] == $comment['coid']) {
          $children[] = $child['coid'];
        }
      }
      $comments[] = array(
        'id' => $comment['coid'], // 评论的id
        'pid' => $comment['cid'],
        'parent' => $comment['parent'],
        'children' => $children,
        'text' => $comment['text'],
        'author' => $comment['author'],
        'email' => $comment['mail'],
        'url' => $comment['url'],
        'status' => $comment['status'],
        'created' => date('c', $comment['created']),
      );
    }

    foreach ($tpPageComments as $comment) {
      $children = array();
      foreach ($tpPageComments as $child) {
        if ($child['parent'] == $comment['coid']) {
          $children[] = $child['coid'];
        }
      }
      $comments[] = array(
        'id' => $comment['coid'], // 评论的id
        'pid' => $comment['cid'],
        'parent' => $comment['parent'],
        'children' => $children,
        'text' => $comment['text'],
        'author' => $comment['author'],
        'email' => $comment['mail'],
        'url' => $comment['url'],
        'status' => $comment['status'],
        'created' => date('c', $comment['created']),
      );
    }

    /**
     * 构造用户数据
     * username, nickname, description, avatar, email, url
     */
    $user = array(
      'username' => $tpUser['name'],
      'nickname' => $tpUser['screenName'],
      'description' => $tpUser['description'],
      'avatar' => $tpUser['avatar'],
      'email' => $tpUser['mail'],
      'url' => $tpUser['url'],
    );

    /**
     * 构造友链数据
     * name, link, desc, logo, group
     */
    $friends = array();
    foreach ($tpLinks as $link) {
      $friends[] = array(
        'name' => $link['name'],
        'link' => $link['url'],
        'desc' => $link['description'],
        'logo' => $link['image'],
        'group' => $link['sort'],
      );
    }

    // 备份文件名
    $fileName = 'mog-data-export-' . strtotime("now") * 1000 . '.json';
    header('Content-Type: application/json');
    header('Content-Disposition: attachment; filename=' . $fileName);
    echo json_encode(array(
      'categories' => $categories,
      'posts' => $posts,
      'pages' => $pages,
      'comments' => $comments,
      'user' => $user,
      'friends' => $friends,
    ), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

    return;
  }

  /**
   * 绑定动作
   *
   * @access public
   * @return void
   */
  public function action()
  {
    $this->widget('Widget_User')->pass('administrator');
    $this->on($this->request->is('export'))->doExport($this->request);
  }
}