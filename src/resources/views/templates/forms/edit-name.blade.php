<form id="action-form" method="post" lf-attribute="action:action">
    <input type="hidden" name="type" lf-attribute="value:type">
    <input type="hidden" name="route" lf-attribute="value:route">
    <input type="hidden" name="_token" lf-attribute="value:_token">
    <br>
    <label for="filename">File name</label>
    <input class="filemanager-input" type="text" id="filename" name="rename"  lf-attribute="value:filename">
    <br>
    <button type="submit" class="button button-blue">Save</button>
</form>