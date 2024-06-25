<?php
if(isset($_GET['id']) && $_GET['id'] > 0){
    $qry = $conn->query("SELECT * from `products` where id = '{$_GET['id']}' ");
    if($qry->num_rows > 0){
        foreach($qry->fetch_assoc() as $k => $v){
            $$k = stripslashes($v);
        }
    }
}

// Fetching subcategories
$sub_categories = array();
$qry = $conn->query("SELECT * FROM `sub_categories` ORDER BY sub_category ASC");
while($row = $qry->fetch_assoc()){
    $sub_categories[$row['parent_id']][] = $row;
}
$sub_categories_json = json_encode($sub_categories);
?>


<div class="card card-outline card-info">
    <div class="card-header">
        <h3 class="card-title"><?php echo isset($id) ? "Update ": "Create New " ?> Product</h3>
    </div>
    <div class="card-body">
        <form action="" id="product-form">
            <input type="hidden" name="id" value="<?php echo isset($id) ? $id : '' ?>">
            
            <!-- Brand Selection -->
            <div class="form-group">
                <label for="brand_id" class="control-label">Brand</label>
                <select name="brand_id" id="brand_id" class="custom-select select2" required>
                    <option value=""></option>
                    <?php
                        $qry = $conn->query("SELECT * FROM `brands` ORDER BY `name` ASC");
                        while($row = $qry->fetch_assoc()):
                    ?>
                    <option value="<?php echo $row['id'] ?>" <?php echo isset($brand_id) && $brand_id == $row['id'] ? 'selected' : '' ?>><?php echo $row['name'] ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <!-- Category Selection -->
            <div class="form-group">
                <label for="category_id" class="control-label">Category</label>
                <select name="category_id" id="category_id" class="custom-select select2" required>
                    <option value=""></option>
                    <?php
                        $qry = $conn->query("SELECT * FROM `categories` ORDER BY category ASC");
                        while($row = $qry->fetch_assoc()):
                    ?>
                    <option value="<?php echo $row['id'] ?>" <?php echo isset($category_id) && $category_id == $row['id'] ? 'selected' : '' ?>><?php echo $row['category'] ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <!-- Sub Category Selection -->
            <div class="form-group">
                <label for="sub_category_id" class="control-label">Sub Category</label>
                <select name="sub_category_id" id="sub_category_id" class="custom-select">
                    <option value="" selected disabled>Select Category First</option>
                </select>
            </div>
            
            <!-- Product Name Input -->
            <div class="form-group">
                <label for="name" class="control-label">Product Name</label>
                <input type="text" name="name" id="name" class="form-control rounded-0" required value="<?php echo isset($name) ? $name : '' ?>" />
            </div>
            
            <!-- Product Specs Input -->
            <div class="form-group">
                <label for="specs" class="control-label">Specs</label>
                <textarea name="specs" id="specs" cols="30" rows="2" class="form-control form no-resize summernote"><?php echo isset($specs) ? $specs : ''; ?></textarea>
            </div>
            
            <!-- Status Selection -->
            <div class="form-group">
                <label for="status" class="control-label">Status</label>
                <select name="status" id="status" class="custom-select">
                    <option value="1" <?php echo isset($status) && $status == 1 ? 'selected' : '' ?>>Active</option>
                    <option value="0" <?php echo isset($status) && $status == 0 ? 'selected' : '' ?>>Inactive</option>
                </select>
            </div>
            
            <!-- Images Upload -->
            <div class="form-group">
                <label for="" class="control-label">Images</label>
                <div class="custom-file">
                    <input type="file" class="custom-file-input rounded-circle" id="customFile" name="img[]" multiple accept=".png,.jpg,.jpeg" onchange="displayImg(this,$(this))">
                    <label class="custom-file-label" for="customFile">Choose file</label>
                </div>
            </div>
            
            <!-- Existing Images Display -->
            <?php if(isset($id)):
                $upload_path = "uploads/product_" . $id;
                if(is_dir(base_app . $upload_path)):
                    $files = scandir(base_app . $upload_path);
                    foreach($files as $img):
                        if(in_array($img, array('.', '..'))) continue;
            ?>
            <div class="d-flex w-100 align-items-center img-item">
                <span><img src="<?php echo base_url . $upload_path . '/' . $img ?>" width="150px" height="100px" style="object-fit:cover;" class="img-thumbnail" alt=""></span>
                <span class="ml-4"><button class="btn btn-sm btn-default text-danger rem_img" type="button" data-path="<?php echo base_app . $upload_path . '/' . $img ?>"><i class="fa fa-trash"></i></button></span>
            </div>
            <?php endforeach; endif; endif; ?>
        </form>
    </div>
    
    <div class="card-footer">
        <button class="btn btn-flat btn-primary" form="product-form">Save</button>
        <a class="btn btn-flat btn-default" href="?page=product">Cancel</a>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/js/select2.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.18/summernote.min.js"></script>
<script>
    var sub_categories = <?php echo $sub_categories_json; ?>;

    function displayImg(input, _this) {
        var fnames = [];
        Object.keys(input.files).forEach(k => {
            fnames.push(input.files[k].name);
        });
        _this.siblings('.custom-file-label').html(fnames.join(', '));
    }

    function delete_img($path) {
        start_loader();

        $.ajax({
            url: _base_url_ + 'classes/Master.php?f=delete_img',
            data: { path: $path },
            method: 'POST',
            dataType: 'json',
            error: err => {
                console.log(err);
                alert_toast("An error occurred while deleting an Image", "error");
                end_loader();
            },
            success: function(resp) {
                $('.modal').modal('hide');
                if (typeof resp == 'object' && resp.status == 'success') {
                    $('[data-path="' + $path + '"]').closest('.img-item').hide('slow', function() {
                        $('[data-path="' + $path + '"]').closest('.img-item').remove();
                    });
                    alert_toast("Image Successfully Deleted", "success");
                } else {
                    console.log(resp);
                    alert_toast("An error occurred while deleting an Image", "error");
                }
                end_loader();
            }
        });
    }

    $(document).ready(function() {
        $('.rem_img').click(function() {
            _conf("Are you sure to delete this image permanently?", 'delete_img', ["'" + $(this).attr('data-path') + "'"]);
        });

        $('#category_id').change(function() {
            var cid = $(this).val();
            var opt = "<option value='' disabled selected>Select Subcategory</option>";
            if (sub_categories[cid]) {
                sub_categories[cid].forEach(function(sub_cat) {
                    opt += "<option value='" + sub_cat.id + "'>" + sub_cat.sub_category + "</option>";
                });
            }
            $('#sub_category_id').html(opt);
            $('#sub_category_id').select2({ placeholder: "Please Select here", width: "relative" });
        });

        $('.select2').select2({ placeholder: "Please Select here", width: "relative" });

        if (parseInt("<?php echo isset($category_id) ? $category_id : 0 ?>") > 0) {
            start_loader();
            setTimeout(() => {
                $('#category_id').trigger("change");
                end_loader();
            }, 750);
        }

        $('#product-form').submit(function(e) {
            e.preventDefault();
            var _this = $(this);
            $('.err-msg').remove();
            start_loader();

            $.ajax({
                url: _base_url_ + "classes/Master.php?f=save_product",
                data: new FormData($(this)[0]),
                cache: false,
                contentType: false,
                processData: false,
                method: 'POST',
                type: 'POST',
                dataType: 'json',
                error: err => {
                    console.log(err);
                    alert_toast("An error occurred", 'error');
                    end_loader();
                },
                success: function(resp) {
                    if (typeof resp == 'object' && resp.status == 'success') {
                        location.href = "./?page=product";
                    } else if (resp.status == 'failed' && !!resp.msg) {
                        var el = $('<div>')
                            el.addClass("alert alert-danger err-msg").text(resp.msg);
                        _this.prepend(el);
                        el.show('slow');
                        $("html, body").animate({ scrollTop: _this.closest('.card').offset().top }, "fast");
                        if (!!resp.id) $('[name="id"]').val(resp.id);
                        end_loader();
                    } else {
                        alert_toast("An error occurred", 'error');
                        end_loader();
                        console.log(resp);
                    }
                }
            });
        });

        $('.summernote').summernote({
            height: 200,
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'italic', 'underline', 'strikethrough', 'superscript', 'subscript', 'clear']],
                ['fontsize', ['fontsize']],
                ['color', ['color']],
                ['para', ['ol', 'ul', 'paragraph']],
                ['table', ['table']],
                ['view', ['undo', 'redo', 'codeview', 'help']]
            ]
        });
    });
</script>
