<table class="timeline<?php if (!empty($param['className']) && preg_match('/[\w ]{1,100}/', $param['className'])) echo " ", $param['className'];?>">
    <caption>Timeline Table</caption>
    <thead>
    <tr>
        <th>Timeline</th>
        <th>Message</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($timelines as $timeline) { ?>
    <tr>
        <td><?php echo $timeline['time']; ?> ms</td>
        <td><?php echo HTML::escape($timeline['message']);?></td>
    </tr>
    <?php } ?>
    </tbody>
</table>