
<div class="wrap">
    <div class="row">
        <h2>{if $messageType == 'error'}出错啦！{elseif $messageType == 'success'}操作成功{elseif $messageType == 'warning'}警告信息{else}提示信息{/if}</h2>
        <p>{$message}</p>
    </div>
    {if $urlForwards}
    <div class="row alignright">
        <ul class="pagination">
        {foreach from=$urlForwards item=link}
            <li><a href="{$link.url}">{$link.text}</a></li>
        {/foreach}
        </ul>
        {if $redirectTime > 0}
        <p>{$redirectTime} 秒后自动跳转。。。</p>
        {/if}
    </div>
    {/if}
</div>

{if $redirectTime > 0 && $redirectUrl}
<script type="text/javascript">
    // <![CDATA[
    setTimeout(function() { location.href="{$urlForwards['0']['url']}".replace(/\&amp\;/g, '&'); }, {$redirectTime*1000});
    // ]]>
</script>
{/if}