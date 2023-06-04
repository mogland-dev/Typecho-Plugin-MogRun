<?php
include_once 'common.php';
include 'header.php';
include 'menu.php';

$db = Typecho_Db::get();
$prefix = $db->getPrefix();
$users_table = $prefix . 'users';
$sql = "SELECT * FROM {$users_table}";
$tpUsers = $db->fetchAll($db->query($sql));
?>

<div class="main">
  <div class="body container">
    <div class="typecho-page-title">
      <h2><?php _e('Migration Typecho Data To Mog'); ?></h2>
    </div>
    <div class="row typecho-page-main" role="form">
      <div id="dbmanager-plugin" class="col-mb-12 col-tb-8 col-tb-offset-2">
        <p>在您点击下面的按钮后，Typecho 会创建一个 JSON 文件，供您保存到计算机中。</p>
        <p>导入的时间会随着数据的增加而增加，如果文章和评论数据比较多的请耐心等待提示。</p>
        <p>使用过程中如果有问题，请到 <a href="https://github.com/mogland-dev/typecho-plugin-mogrun/issues">GitHub</a> 提出。</p>
        <p>选择你要导出的内容，点击导出按钮。下载导出的 zip 文件，解压后，将其中的 data.json 文件放入 Mog 后台的迁移页面，点击导入按钮。等待导入完成，即可完成迁移。</p>
        
        <form action="<?php $options->index('/action/MogRun?export'); ?>" method="post">
        <h3>选择管理用户</h3>
        <?php 
          foreach ($tpUsers as $user) {
            echo '<input name="uid" type="radio" value="' . $user['uid'] . '" id="uid-' . $user['uid'] . '" /> <label for="uid-' . $user['uid'] . '">' . $user['name'] . '</label>';
          }
        ?>
        <div style="margin-top: 20px;">
          <button type="submit" class="btn primary"><?php _e('导出 JSON 迁移文件'); ?></button>
        </div>
        </form>

      </div>
    </div>
  </div>
</div>

<style>
/* 样式 radio */
input[type="radio"] {
  position: relative;
  appearance: none;
  -webkit-appearance: none;
  -moz-appearance: none;
  width: 16px;
  height: 16px;
  border-radius: 50%;
  border: 2px solid #555555;
  outline: none;
  transition: 0.2s;
  cursor: pointer;
}

input[type="radio"]::before {
  content: "";
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background-color: #555555;
  transition: 0.2s;
  opacity: 0;
}

input[type="radio"]:checked::before {
  opacity: 1;
}

/* 样式 checkbox */
input[type="checkbox"] {
  position: relative;
  appearance: none;
  -webkit-appearance: none;
  -moz-appearance: none;
  width: 16px;
  height: 16px;
  border-radius: 4px;
  border: 2px solid #555555;
  outline: none;
  transition: 0.2s;
  cursor: pointer;
}

input[type="checkbox"]::before {
  content: "\2713";
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  font-size: 12px;
  color: #555555;
  transition: 0.2s;
  opacity: 0;
}

input[type="checkbox"]:checked::before {
  opacity: 1;
}


.checkbox-group span {
  margin-right: 10px;
}
</style>
<?php
include 'copyright.php';
include 'common-js.php';
include 'table-js.php';
include 'footer.php';
?>