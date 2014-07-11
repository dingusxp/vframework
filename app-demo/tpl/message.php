
<div class="wrap">
    <div class="row">
        <h2>
		<?php if ($messageType == 'error') { ?>出错啦！
		<?php } elseif ($messageType == 'success') { ?>操作成功
		<?php } elseif ($messageType == 'warning') { ?>警告信息
		<?php } else { ?>提示信息
		<?php } ?></h2>
        <p><?php echo $message;?></p>
    </div>
    <?php if ($urlForwards) { ?>
    <div class="row alignright">
        <ul class="pagination">
        <?php foreach ($urlForwards as $item=>$link) { ?>
            <li><a href="<?php $link['url'];?>"><?php echo $link['text'];?></a></li>
        <?php } ?>
        </ul>
        <?php if ($redirectTime > 0) { ?>
        <p><?php echo $redirectTime;?> 秒后自动跳转。。。</p>
        <?php } ?>
    </div>
    <?php } ?>
</div>

<?php if ($redirectTime > 0 && $redirectUrl) { ?>
<script type="text/javascript">
    // <![CDATA[
    setTimeout(function() { location.href="<?php echo $urlForwards['0']['url'];?>".replace(/\&amp\;/g, '&'); }, <?php echo intval($redirectTime*1000);?>);
    // ]]>
</script>
<?php } ?>