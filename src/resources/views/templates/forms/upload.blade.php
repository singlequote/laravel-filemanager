<div>

    <form id="action-form" method="post">
        <input type="hidden" name="_token" lf-attribute="value:_token">
        <input type="hidden" name="directory" lf-attribute="value:directory">
        <br>
        <label>Create thumbnails</label><br>
        <label for="thumb-150">
            <input class="filemanager-input" type="checkbox" id="thumb-150" name="thumb[150]">
            150 X 150
        </label><br>
        <label for="thumb-300">
            <input class="filemanager-input" type="checkbox" id="thumb-300" name="thumb[300]">
            300 X 300
        </label><br>
        <label for="thumb-500">
            <input class="filemanager-input" type="checkbox" id="thumb-500" name="thumb[500]">
            500 X 500
        </label><br>
        <label for="thumb-800">
            <input class="filemanager-input" type="checkbox" id="thumb-800" name="thumb[800]">
            800 X 800
        </label>
    </form>

    <button class='button button-blue' onclick='$(`#action-form`).trigger(`click`)'>Click or drag and drop files to upload</button>
    
</div>