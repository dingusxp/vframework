<?php if ($links) { ?>
<div class="pagescontainer">
<?php foreach ($links as $link) { ?>
    <?php if ($link['type'] == 'link') { ?>
        <?php if ($link['is_current']) { ?>
        <span class="current"><?php echo $link['text']; ?></span>
        <?php } else { ?>
        <a href="<?php echo $link['href'];?>"><?php echo $link['text']; ?></a>
        <?php } ?>
    <?php } else { ?>
    <span class="ghost"><?php echo $link['text']; ?></span>
    <?php } ?>
<?php } ?>
</div>
<?php } ?>