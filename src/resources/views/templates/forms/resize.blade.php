<div>
    <form id="action-form" method="post" lf-attribute="action:url">
        <input type="hidden" name="_token" lf-attribute="value:_token">
        <input type="hidden" name="path" lf-attribute="value:path">
        <input type="hidden" name="width">
        <input type="hidden" name="origin_width">
        <input type="hidden" name="height">
        <input type="hidden" name="origin_height">
        <input type="hidden" name="scale">
        Height :<label class="resize-width"></label>
        Width :<label class="resize-height"></label>
        <button style="float:right" class="button button-blue">Save</button>
    </form>
    <div  id="resizer" >
        <div id="image-resize">
            <img lf-attribute="src:route">
        </div>
        <div id="preview-resize">
            <div id="preview-frame"></div>
            <img lf-attribute="src:route">
        </div>
    </div>
</div>