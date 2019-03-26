<div>
    <form id="action-form" method="post" lf-attribute="action:url">
        <input type="hidden" name="_token" lf-attribute="value:_token">
        <input type="hidden" name="path" lf-attribute="value:path">
        <input type="hidden" name="width">
        <input type="hidden" name="origin_width">
        <input type="hidden" name="height">
        <input type="hidden" name="origin_height">
        <input type="hidden" name="scale">

        <button style="float:right" class="button button-blue">Save</button>
    </form>

    <div id="image-resize">
        Height :<label class="resize-width">500px</label>
        Width :<label class="resize-height">500px</label>
        <img lf-attribute="src:route">
    </div>
</div>