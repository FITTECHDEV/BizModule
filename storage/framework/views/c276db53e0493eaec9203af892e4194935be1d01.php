

<?php $__env->startSection('title', 'Dashboard'); ?>

<?php $__env->startSection('content_header'); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="container-fluid p-2">
        <div class="row justify-content-center">
            <div class="col-12 col-sm-12 col-md-12">
                <div class="" id="create-ticket-card">
                    <div class="">
                        <form action="<?php echo e(route('tickets.store')); ?>" method="POST" class="form-horizontal"
                            id="addTicketForm" enctype="multipart/form-data">
                            <?php echo csrf_field(); ?>
                            <div class="row justify-content-center">
                                <div class="col-md-7 col-sm-9 col-9 shadow-lg ml-2 p-2">
                                    <h4 class="custom_title">Raise a Ticket</h4>
                                    <div class="">
                                        <div class="form-group">
                                            <label for="ticket_title" class="control-label">Ticket Title <span
                                                    class="text-danger">*</span></label>
                                            <div class="">
                                                <input type="text" name="ticket_title" id="ticket_title"
                                                    class="form-control form-control-sm <?php $__errorArgs = ['ticket_title'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                                    value="<?php echo e(old('ticket_title')); ?>" placeholder="Ticket Title" required>
                                                <?php $__errorArgs = ['ticket_title'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                    <span class="invalid-feedback">
                                                        <strong><?php echo e($message); ?></strong>
                                                    </span>
                                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="ticket_description" class="control-label">Ticket Description <span
                                                    class="text-danger">*</span></label>
                                            <div class="">
                                                <textarea name="ticket_description" id="ticket_description"
                                                    class="form-control form-control-sm <?php $__errorArgs = ['ticket_description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                                    placeholder="Tell us about this ticket" required><?php echo e(old('ticket_description')); ?></textarea>
                                                <?php $__errorArgs = ['ticket_description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                    <span class="invalid-feedback">
                                                        <strong><?php echo e($message); ?></strong>
                                                    </span>
                                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                            </div>
                                        </div>
                                        <div class="row justify-content-center mb-2">
                                            
                                            <div class="col-md-3">
                                                <label for="solution">Solution <span class="text-danger">*</span></label>
                                                <select name="solution" id="solution"
                                                    class="form-control <?php $__errorArgs = ['solution'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" required>
                                                    <option value="" disabled selected>Select Solution</option>
                                                    <?php $__empty_1 = true; $__currentLoopData = $solutions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $solution): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                                        
                                                        <option value="<?php echo e($solution->ID); ?>">
                                                            <?php echo e($solution->CategoryName); ?>

                                                        </option>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                                        <option value="" disabled>No Available Solutions</option>
                                                    <?php endif; ?>
                                                </select>
                                                <?php $__errorArgs = ['solution'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                    <span class="invalid-feedback">
                                                        <strong> <?php echo e($message); ?></strong>
                                                    </span>
                                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                            </div>
                                            <div class="col-md-3">
                                                <label for="module">Category<span
                                                        class="text-danger">*</span></label>
                                                <select name="module" id="module"
                                                    class="form-control <?php $__errorArgs = ['module'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" required>
                                                    <option value="" disabled selected>Select Category</option>
                                                </select>
                                                <?php $__errorArgs = ['module'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                    <span class="invalid-feedback">
                                                        <strong> <?php echo e($message); ?></strong>
                                                    </span>
                                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                            </div>
                                            <div class="col-md-3">
                                                <label for="sub_module">Sub Category<span
                                                        class="text-danger">*</span></label>
                                                <select name="sub_module" id="sub_module"
                                                    class="form-control <?php $__errorArgs = ['sub_module'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" required>
                                                    <option value="" disabled selected>Select Sub Category</option>
                                                </select>
                                                <?php $__errorArgs = ['sub_module'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                    <span class="invalid-feedback">
                                                        <strong> <?php echo e($message); ?></strong>
                                                    </span>
                                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                            </div>

                                            
                                            <div class="col-md-3">
                                                <label for="priority">Priority <span class="text-danger">*</span></label>
                                                <select name="priority" id="priority"
                                                    class="form-control <?php $__errorArgs = ['priority'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" required>
                                                    <option value="" disabled selected>Select Priority</option>
                                                    <?php $__empty_1 = true; $__currentLoopData = $priorities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $priority): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                                        <option value="<?php echo e($priority->ID); ?>" <?php if(old('priority') == $priority->ID): ?> selected <?php endif; ?>>
                                                            <?php echo e($priority->CategoryName); ?></option>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                                        <option value="" disabled>No Available Priorities</option>
                                                    <?php endif; ?>
                                                </select>
                                                <?php $__errorArgs = ['priority'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                    <span class="invalid-feedback">
                                                        <strong> <?php echo e($message); ?></strong>
                                                    </span>
                                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                            </div>
                                        </div>
                                        <div class="row mt-2">
                                            <div class="col-md-7 col-sm-7">
                                                    <div class="form-group">
                                                    <label for="ticket_attachments"
                                                        class="control-label">Attachment(s)</label>
                                                    <button type="button"
                                                        class="add_field_button btn btn-xs btn-outline-success ml-5">
                                                        <i class="fas fa-plus"></i> Add Files</button>
                                                    <div class="">
                                                        <div class="input_fields_wrap input-group">
                                                            <div>
                                                                <input type="file" name="attachments[]"
                                                                    value="<?php echo e(old('attachments')); ?>" id="attach"
                                                                    class="attach form-control form-control-sm <?php $__errorArgs = ['attachments.*'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                                                <?php $__errorArgs = ['attachments.*'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                                    <span class="invalid-feedback">
                                                                        <strong> <?php echo e($message); ?></strong>
                                                                    </span>
                                                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                                            </div>
                                                            <div class="input-group-append clear" id="clear">
                                                                <button class="btn btn-xs btn-outline-warning"
                                                                    id="clearBtn">Clear</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-5 col-sm-5">
                                                <div class="form-group">
                                                    <label for="">Office <span class="text-danger">*</span></label>
                                                    <?php if(Auth::user()->can('raise-ticket-for-any-office')): ?>
                                                        <select name="OfficeID" id="OfficeID"
                                                            class="form-control <?php $__errorArgs = ['OfficeID'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                                            required>
                                                            <option value="" selected disabled>Select an Office</option>
                                                            <?php $__empty_1 = true; $__currentLoopData = \App\Models\Office::all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                                                <option value="<?php echo e($item->ID); ?>"
                                                                    <?php if(old('OfficeID') == $item->ID): ?> selected <?php endif; ?>><?php echo e($item->Name); ?>

                                                                </option>
                                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                                                <option value="" disabled>No Offices</option>
                                                            <?php endif; ?>
                                                        </select>

                                                    <?php else: ?>
                                                        <input type="hidden" name="OfficeID" id="OfficeID"
                                                            class="form-control" value="<?php echo e($user_data->Office->ID); ?>">
                                                        <input type="text" name="OfficeName" id="OfficeName"
                                                            class="form-control" value="<?php echo e($user_data->Office->Name); ?>"
                                                            disabled>
                                                    <?php endif; ?>
                                                    <?php $__errorArgs = ['OfficeID'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                        <span class="invalid-feedback">
                                                            <strong> <?php echo e($message); ?></strong>
                                                        </span>
                                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                                </div>
                                            </div>
                                        </div>


                                    </div>

                                    
                                    <div class="row justify-content-center">
                                        <div class="col-md-6">

                                            <button class="btn mt-4 mb-3 btn-block btn-outline-success shadow"
                                                id="raiseTicketBtn">
                                                <i class="fas fa-fist-raised"></i>
                                                Raise Ticket
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2 col-sm-3 col">
                                    <a href="<?php echo e(route('tickets.my_tickets')); ?>" class="btn btn-primary float-right"><i
                                            class="fas fas fa-home"></i> Home</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('css'); ?>
    <style>
        .custom_title {
            overflow: hidden;
            text-align: center;
        }

        .custom_title:before,
        .custom_title:after {
            background-color: #000;
            content: "";
            display: inline-block;
            height: 1px;
            position: relative;
            vertical-align: middle;
            width: 50%;
        }

        .custom_title:before {
            right: 0.5em;
            margin-left: -50%;
        }

        .custom_title:after {
            left: 0.5em;
            margin-right: -50%;
        }

    </style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('js'); ?>
    
    <script src="<?php echo e(asset('js/jquery.remember-state.js')); ?>"></script>
    <script>
        $(function() {
            $('#raiseTicketBtn').attr('disabled', false);
            $('#clear').hide();

            $('#raiseTicketBtn').click(function() {
               $(this).attr('disabled', true);
                /*  // */
                $('#addTicketForm').submit();
            });

            $('#addTicketForm').on('submit', function(){
                $('#raiseTicketBtn').attr('disabled', true);
                $('#raiseTicketBtn').text('Creating ...');
            });

            var max_fields = 2;
            var wrapper = $(".input_fields_wrap");
            var add_button = $(".add_field_button");

            var t = 1;
            $(add_button).click(function(e) {
                e.preventDefault();
                /*loop through the attachments check weather there is nulls prevent creation of new input tag*/
                let input_tag = document.getElementById('attach').files.length;
                $("input[id*='attach']").each(function() {
                    if (input_tag < 1) {
                        toastr.warning('Kindly upload a File to add more.');
                    } else {
                        if (t < max_fields) {
                            $(wrapper).append(
                                '<div class="input-group m-2"><input type="file" name="attachments[]" id="attach" class="attach form-control"/><a href="#" class="remove_field btn btn-sm btn-outline-danger input-group-append">Remove</a></div>'
                                );
                            t++;
                        } else {
                            toastr.info('That\'s it on files.');
                        }
                    }
                });
            });
            $('#attach').on('change', function() {
                $('#clear').show();
            });

            $('#clearBtn').on('click', function() {
                $('#attach').val('');
                $('#clear').hide();
            });


            $(wrapper).on("click", ".remove_field", function(e) {
                e.preventDefault();
                $(this).parent('div').remove();
                t--;
            });


            $('#module').attr('disabled', true);
            $('#sub_module').attr('disabled', true);
            $('#solution').on('change', function() {
                $('#module').attr('disabled', true);
                $('#sub_module').attr('disabled', true);
              var  sol = $('#solution option:selected').val();
                fetch_modules(sol);
            });

            $('#module').on('change', function() {
                $('#sub_module').attr('disabled', true);
                /*save this option
                localStorage.setItem('module_val',$(this).val());
                localStorage.setItem('module_text',$('#module option:selected').html());*/
                $.ajax({
                    url: '<?php echo e(route('modules.get_data')); ?>',
                    type: 'GET',
                    datatype: 'json',
                    data: {
                        ID: $('#module option:selected').val(),
                        check: 'module',
                    },
                    success: function(data) {

                        $('#sub_module').attr('disabled', false);
                        $('#sub_module').attr('required', true);

                        $('#sub_module').empty().append(
                            '<option value="" selected disabled>Select Category</option>'
                        );

                        $.each(data, function(index, value) {

                            $('#sub_module').append($('<option/>', {
                                value: value.ID,
                                text: value.CategoryName
                            }));
                        });
                    },
                    error: function(error) {}

                });

            });

            /* $('#sub_module').on('change', function() {
            //     localStorage.setItem('sub_module_val',$(this).val());
            //     localStorage.setItem('sub_module_text',$('#sub_module option:selected').html());
            // });*/

            /*Fetch Modules*/
            function fetch_modules(solutionId){
                $.ajax({
                    url: "<?php echo e(route('modules.get_data')); ?>",
                    type: 'GET',
                    datatype: 'json',
                    data: {
                        ID: solutionId,
                        check: 'solution',
                    },
                    success: function(data) {

                        $('#module').attr('disabled', false);
                        $('#module').attr('required', true);
                        $('#module').empty().append(
                            '<option value="" selected disabled>Sub-Category Name</option>'
                        );

                        $.each(data, function(index, value) {

                            /**/$('#module').append($('<option/>', {
                                value: value.ID,
                                text: value.CategoryName
                            }));

                        });

                    },
                    error: function(error) {
                        console.log(error);
                    }

                });
            }
        });
    </script>
    

    <script src="https://cdn.ckeditor.com/ckeditor5/23.0.0/classic/ckeditor.js"></script>

    <script>
        ClassicEditor.create( document.querySelector( '#ticket_description' ) )
            .catch( error => {
                console.error( error );
            } );
    </script>


<?php $__env->stopSection(); ?>

<?php echo $__env->make('adminlte::page', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\JosephSifuna\fit_service_desk\resources\views/tickets/create.blade.php ENDPATH**/ ?>