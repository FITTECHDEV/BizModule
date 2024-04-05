

<?php $__env->startSection('title', 'Category-Dashboard'); ?>

<?php $__env->startSection('content_header'); ?>
    <div class="text-center">
        <span class="h1 mr-4">Category Dashboard</span> <button id="addSolutionbtn"
            class="btn btn-xs btn-outline-info"><i class="fas fa-plus"></i> Add New Solution</button>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="container">

        <?php $__currentLoopData = $solutions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $solution): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="card card-header shadow">
                <div class="text-uppercase text-bold h2 "><span href="" class="updateSubmodule" data-name="CategoryName" data-type="text"
                        data-pk="<?php echo e($solution->ID); ?>" data-title="Enter Name"><?php echo e($solution->CategoryName); ?></span>
                    <button type="button" id="addCategory" value="<?php echo e($solution->ID); ?>"
                        class="addCategory btn btn-xs btn-success float-right"><i class="fas fa-plus"></i> Add
                        Category</button>
                </div>
            </div>
            <div class="row mr-2">
                <?php $__empty_1 = true; $__currentLoopData = $modules; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $moduelKey => $module): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php if($module->ParentCategoryID == $solution->ID): ?>
                        <div class="col-md-3 card card-body shadow mr-1">
                            <div class=" h3"> <span href="" class="updateSubmodule" data-name="CategoryName"
                                    data-type="text" data-pk="<?php echo e($module->ID); ?>"
                                    data-title="Enter Name"><?php echo e($module->CategoryName); ?></span>
                                <button type="button" id="addSubCategory" value="<?php echo e($module->ID); ?>"
                                    class="addSubCategory btn btn-xs btn-outline-success float-right">
                                    <i class="fas fa-plus"></i> Add
                                </button>
                            </div>
                            <?php $__empty_2 = true; $__currentLoopData = $submodules; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subModuleKey => $subModule): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_2 = false; ?>
                                <?php if($subModule->ParentCategoryID == $module->ID): ?>
                                    <span href="" class="updateSubmodule" data-name="CategoryName" data-type="text"
                                        data-pk="<?php echo e($subModule->ID); ?>"
                                        data-title="Enter Name"><?php echo e($subModule->CategoryName); ?></span>
                                <?php endif; ?>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_2): ?>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <?php endif; ?>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <!-- Button trigger modal -->
            <span data-toggle="modal" data-target="#addSolutionModal" id="addSolutionModalOpen"></span>
            
            <!-- Add Solution Modal-->
            <div class="modal fade" id="addSolutionModal" data-backdrop="static" tabindex="-1" role="dialog"
                aria-labelledby="addSolutionTitleLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="addSolutionTitleLabel">Add Category</h5>
                            
                        </div>
                        <form id="addCategoryForm">
                            <?php echo csrf_field(); ?>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group" id="SolutionCodeDiv">
                                            <label for="SolutionCode">Solution Code</label>
                                            <input type="text" name="SolutionCode" id="SolutionCode"
                                                class="SolutionCode form-control" placeholder="Solution Code">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group" id="SolutionIdDiv">
                                            <label for="SolutionName">Solution</label>
                                            <input type="text" name="SolutionName" id="SolutionName"
                                                class="SolutionName form-control" placeholder="Solution Name">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group" id="CategoryCodeDiv">
                                            <label for="CategoryCode">Category Code</label>
                                            <input type="text" name="CategoryCode" id="CategoryCode"
                                                class="CategoryCode form-control" placeholder="Category Code">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group" id="CategoryIdDiv">
                                            <label for="CategoryName">Category</label>
                                            <input type="text" name="CategoryName" id="CategoryName"
                                                class="CategoryName form-control" placeholder="Category Name">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group" id="SubCategoryCode">
                                            <label for="SubCategoryCode">Sub Category Code</label>
                                            <input type="text" name="SubCategoryCode" id="SubCategoryCode"
                                                class="form-control" placeholder="Sub Module Code">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group" id="SubCategoryNameDiv">
                                            <label for="SubCategoryName">Sub Category Name</label>
                                            <input type="text" name="SubCategoryName" id="SubCategoryName"
                                                class="form-control" placeholder="Sub Category Name">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal"
                                    id="closeCategoryModalButton">Close</button>
                                <button type="submit" class="btn btn-success  btn-sm" id="saveCategoryButton">Save
                                    Changes</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <?php $__env->stopSection(); ?>

    <?php $__env->startSection('css'); ?>
        <link href="//cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.0/jquery-editable/css/jquery-editable.css"
            rel="stylesheet" />

    <?php $__env->stopSection(); ?>

    <?php $__env->startSection('js'); ?>

        <script>
            $.fn.poshytip = {
                defaults: null
            }
        </script>
        <script src="//cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.0/jquery-editable/js/jquery-editable-poshytip.min.js">
        </script>
        <script>
            $.fn.editable.defaults.mode = 'inline';
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
                }
            });

            $('.updateSubmodule').editable({
                url: "<?php echo e(route('submodule.update_ajax')); ?>",
                type: 'text',
                //* pk: 1,
                // name: 'name',
                // title: 'Enter name'*/
            });
        </script>
        <script>
            $(function() {
                let newForm = false;
                /*click add category button*/
                $('#addSolutionbtn').click(function() {
                    /*disable and inform*/
                    $('#addSolutionbtn').addClass('disabled');
                    $('#addSolutionbtn').html('<i class="fas fa-spinner fa-spin"></i> Fetching..');
                    /*Show Modal*/
                    $('#addSolutionModalOpen').click();
                    /*Set modal title*/
                    $('#addSolutionTitleLabel').html('Add New Solution ');
                    /*Reset add category button*/
                    setTimeout(() => {
                        $('#addSolutionbtn').removeClass('disabled');
                        $('#addSolutionbtn').html('<i class="fas fa-plus"></i> Add New Solution');
                    }, 2000);
                    /*append the form_type=new status*/
                    $("<input>").attr({
                        name: "form_type",
                        id: "hiddenId",
                        type: "hidden",
                        value: 'solution'
                    }).appendTo("form");

                    $("#SolutionCodeDiv").append(
                        '<input type="hidden" value="new" name="form_type" id="form_type"');
                    $('#CategoryIdDiv, #CategoryCodeDiv, #SubCategoryCode, #SubCategoryNameDiv').css('display',
                        'none');

                });
                /*Close Button*/
                $('#closeCategoryModalButton').click(function() {
                    /*Reset Form*/
                    $('#addCategoryForm')[0].reset();
                    $('#form_type').remove();
                    $('#form_type').remove();
                    $('#oldCategoryID').remove();
                    $('#oldSolutionID, #oldSolutionCode').remove();
                    $('.SolutionCode ,.SolutionName, #CategoryName').attr('disabled', false);
                    $('#CategoryCode').prop('disabled', false);
                    $('#SolutionCodeDiv ,#SolutionIdDiv, #CategoryIdDiv, #CategoryCodeDiv, #SubCategoryCode, #SubCategoryNameDiv')
                        .css('display', '');

                });
                /*Add SubCategory Button*/
                $('.addSubCategory').each(function(i, obj) {
                    $(obj).click(function() {
                        $('#SolutionCodeDiv ,#SolutionIdDiv, #CategoryIdDiv, #CategoryCodeDiv').css(
                            'display', 'none');
                        $('.addSubCategory').addClass('disabled');
                        $('.addSubCategory').html('<i class="fas fa-spinner fa-spin"></i> Fetching..');

                        var solnId = $(obj).attr('value');
                        var url = '/categories/get-category/' + solnId + '/Module';
                        $.get(url, function(data, status) {
                            console.log(data);
                            $('.addSubCategory').removeClass('disabled');
                            $('.addSubCategory').html('<i class="fas fa-plus"></i> Add');

                            if (status == 'success') {
                                $('#addSolutionModalOpen').click();
                                $('#addSolutionTitleLabel').html('Add Sub Category to <b>' +
                                    data.data.CategoryName + '</b> Category');
                                $('input[name=CategoryCode]').val(data.data.CategoryCode);
                                $('input[name=CategoryName]').val(data.data.CategoryName);
                                $('input[name=SolutionCode]').val(data.data.ID);
                            }
                        });
                    });
                });
                $('.addCategory').each(function(i, obj) {
                    $(obj).click(function() {
                        $('.addCategory').addClass('disabled');
                        $('.addCategory').html('<i class="fas fa-spinner fa-spin"></i> Fetching..');
                        var solnId = $(obj).attr('value');
                        console.log(solnId);
                        $('#SubCategoryCode, #SubCategoryNameDiv').css('display','none');
                        var url = "/categories/get-category/" + solnId + "/Solution";
                        $.get(url, function(data, status) {
                            $('.addCategory').removeClass('disabled');
                            $('.addCategory').html('<i class="fas fa-plus"></i> Add Category');
                            if (status == 'success') {
                                /*Open Modal*/
                                $('#addSolutionModalOpen').click();
                                /*Change Title*/
                                $('#addSolutionTitleLabel').html('<h4>Add Category to ' + data
                                    .data.CategoryName + ' Solution</h4>');
                                /*Dissable solution name and code add values*/
                                $('.SolutionCode ,.SolutionName').attr('disabled', true);
                                $('.SolutionCode').val(data.data.CategoryCode);
                                $('.SolutionName').val(data.data.CategoryName);
                                $('#SolutionCodeDiv').append(
                                    '<input name="oldSolutionID" id="oldSolutionID" value=' +
                                    data.data.ID + ' type="hidden"  >');
                                $('#SolutionCodeDiv').append(
                                    '<input name="oldSolutionCode" id="oldSolutionCode" value=' +
                                    data.data.CategoryCode + ' type="hidden"  >');
                                $('#SolutionCodeDiv').append(
                                    '<input name="form_type" id="form_type" value="category" type="hidden"  >'
                                );
                            }
                        });
                    });
                });
                $('#addCategoryForm').submit(function(e) {
                    e.preventDefault();
                    $('#saveCategoryButton').html('<i class="fas fa-spinner fa-spin"></i> Saving..');
                    $('#saveCategoryButton').attr('disabled', true);
                    console.log($(this).serializeArray());
                    /*Process Form*/
                    $.post('/categories/save-category', $(this).serialize(), function(data, status) {
                        if (status == 'success') {
                            $('#saveCategoryButton').html('Save Changes');
                            $('#saveCategoryButton').attr('disabled', false);
                            toastr.success('Category Saved Successfully');
                            $('#addCategoryForm')[0].reset();
                            $('#closeCategoryModalButton').click();
                            location.reload();
                        }

                    });
                });
                /* Edit SubMoidule on the flair*/

            });
        </script>
    <?php $__env->stopSection(); ?>

<?php echo $__env->make('adminlte::page', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\inetpub\wwwroot\erp\resources\views/categories/index.blade.php ENDPATH**/ ?>