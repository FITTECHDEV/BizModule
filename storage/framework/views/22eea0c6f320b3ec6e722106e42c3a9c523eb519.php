<?php $__env->startSection('title', 'Tickets'); ?>

<?php $__env->startSection('content_header'); ?>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12 text-center">
                <div class="card card-timeline px-1 border-none text-center">
                    <div class="row justify-content-center">
                        <div class="col-md-3">
                            <span class="text-uppercase">Ticket Status </span> <br>
                            <span>#<?php echo e(Str::replace(' ', '', $ticket->TicketNo)); ?>:</span> <br>
                            Created: <i class="fas fa-clock"></i> <b><?php echo e($ticket->CreatedOn->diffForHumans()); ?></b></span>

                        </div>
                        <div class="col-md-8">
                            <ul class="bs4-order-tracking p-2" style="<?php if($ticket->Status < 1): ?> display:none <?php endif; ?>">
                                <li class="step <?php if($ticket->Status >= 1): ?> active <?php endif; ?>">
                                    <div><i class="fas fa-hands"></i></div> Open <br>
                                    <span><?php echo e(date('d-m-Y H:i:s', strtotime($ticket->CreatedOn))); ?></span><br>
                                </li>
                                <li class="step <?php if($ticket->Status >= 2): ?> active <?php endif; ?>">
                                    <div><i class="fas fa-check"></i></div> Solved <br>
                                    <?php if($ticket->Status >= 2): ?>
                                        <?php
                                            $date_a = $ticket->CreatedOn;

                                            $date_b = $ticket->messages
                                                ->where('Type', 'Status')
                                                // ->where('PreviousValue', '=', 0)
                                                ->whereIn('PreviousValue', [0, 1])
                                                ->where('NewValue', '=', 2)
                                                ->first();
                                            // dd($date_b);
                                            if ($date_b != null) {
                                                $interval = date_diff($date_a, $date_b->CreatedOn);

                                                echo date('d-m-Y H:i:s', strtotime($date_b->CreatedOn));
                                                // . '<br>(' . $interval->format('%hh:%im:%ss') . ')';
                                            }

                                        ?>
                                    <?php endif; ?>

                                    
                                    
                                </li>
                                <li class="step <?php if($ticket->Status >= 3): ?> active <?php endif; ?>">
                                    <div><i class="fas fa-envelope"></i></div> Closed <br>
                                    <?php if($ticket->Status >= 3): ?>
                                        <?php
                                            $date_b = $ticket->messages
                                                ->where('Type', 'Status')
                                                ->where('PreviousValue', '=', 2)
                                                ->where('NewValue', '=', 3)
                                                ->first();

                                            if ($date_b != null) {
                                                $interval = date_diff($date_a, $date_b->CreatedOn);
                                                echo date('d-m-Y H:i:s', strtotime($date_b->CreatedOn));
                                            }
                                        ?>
                                    <?php endif; ?>
                                    
                                    
                                </li>
                                <?php if($ticket->Status >= 4): ?>
                                    <li class="step <?php if($ticket->Status >= 4): ?> active <?php endif; ?>">
                                        <div class="bg-red"><i
                                                class="fas fa-thumbs-down  <?php if($ticket->Status >= 4): ?>  <?php endif; ?>"></i>
                                        </div>
                                        <span class="<?php if($ticket->Status >= 4): ?> text-danger <?php endif; ?>"> Re-Opened</span> <br>
                                        <?php if($ticket->Status >= 4): ?>
                                            <?php
                                                $date_bc = $ticket->messages
                                                    ->where('Type', 'Status')
                                                    ->where('PreviousValue', '=', 3)
                                                    ->where('NewValue', '=', 4)
                                                    ->first();
                                                if ($date_bc != null) {
                                                    $interval = date_diff($date_a, $date_bc->CreatedOn);
                                                    echo date('d-m-Y H:i:s', strtotime($date_bc->CreatedOn));
                                                }
                                            ?>
                                        <?php endif; ?>
                                        
                                    </li>
                                    <li class="step <?php if($ticket->Status >= 5): ?> active <?php endif; ?>">
                                        <div> <i class="fas fa-smile"></i></div> Settled <br>
                                        <?php if($ticket->Status >= 5): ?>
                                            <?php
                                                $date_b = $ticket->messages
                                                    ->where('Type', 'Status')
                                                    ->where('PreviousValue', '=', 4)
                                                    ->where('NewValue', '=', 5)
                                                    ->first();
                                                if ($date_b != null) {
                                                    $interval = date_diff($date_a, $date_b->CreatedOn);
                                                    echo date('d-m-Y H:i:s', strtotime($date_b->CreatedOn));
                                                }
                                            ?>
                                        <?php endif; ?>
                                    </li>
                                <?php endif; ?>
                            </ul>
                            <div style="<?php if($ticket->Status != 0): ?> display:none <?php endif; ?>">
                                <h3 class="card shadow card-outline card-warning mt-2">
                                    Status : On-Hold
                                </h3>
                                <p> <strong>Comments:</strong>
                                    <?php echo e($ticket->messages->where('TicketID', $ticket->Id)->where('NewValue', '=', 0)->first()['Comment'] ?? ''); ?>

                                </p>
                            </div>
                        </div>
                        <div class="col-md-1 p-2">
                            <a href="<?php echo e(route('tickets.my_tickets')); ?>" class="btn btn-primary btn-sm float-right"><i
                                    class="fas fa-home"></i> Home</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row justify-content-center pt-2">
            <div class="col-md-12">
                <div class="card shadow">
                    <div class="card-header">
                        <div class="card-title">
                            <span class="small">Created By : <b><?php echo e($ticket->user->full_name); ?></b></span> <br>
                            <small class="small userId" id="<?php echo e(Auth::user()->Role); ?>"> Assigned To:
                                <b id="assignedTo"><?php echo e($ticket->assignedTo->full_name ?? 'Not Assigned'); ?></b>
                            </small>
                        </div>
                        <div class="card-tools">
                            <form class="">
                                <input type="hidden" id="ticketId" name="ticketId" value="<?php echo e($ticket->Id); ?>">
                                <div class="form-group">
                                    <select id="changeTicketStatus" name="changeTicketStatus" class="form-control"
                                        <?php if(isset($ticket->office) && $ticket->office->Code === 'ALL' && Auth::user()->Role != 1): ?> disabled <?php endif; ?>>
                                        <option value="" disabled selected>Change Status</option>
                                        <?php if($ticket->Status == 1): ?>
                                            <option value="2">Solved</option>
                                            <option value="3">Closed</option>
                                            <option value="0">On Hold</option>
                                        <?php elseif($ticket->Status == 2): ?>
                                            <option value="1">Open</option>
                                            <option value="3">Closed</option>
                                        <?php elseif($ticket->Status == 3): ?>
                                            <option value="1">Re-Opened</option>
                                            
                                      <?php elseif($ticket->Status == 0): ?>
                                        <option value="2">Solved</option>
                                        <option value="3">Closed</option>
                                        
                                        <?php endif; ?>

                                    </select>
                                </div>
                            </form>
                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('assign-ticket')): ?>
                                <?php if($ticket->Status != 3 && $ticket->Status != 5): ?>
                                    
                                    <input type="hidden" value="<?php if($ticket->assignedTo): ?> Re-Assign <?php else: ?> Assign <?php endif; ?>" id="status">
                                    <!-- Button trigger modal -->
                                    <button type="button" id="modalShowButton" class="btn btn-info btn-sm" data-toggle="modal"
                                        data-target="#assignModal">
                                        <?php if($ticket->assignedTo): ?>
                                        Re-Assign
                                        <?php else: ?> Assign
                                        <?php endif; ?>
                                    </button>
                                    <a target="_blank"
                                        href="http://jira.aurionprosolutions.com:8080/secure/CreateIssue!default.jspa"
                                        class="btn btn-sm btn-primary ml-1">
                                        Vendor Issue
                                    </a>
                                    <!-- Modal -->
                                    <div class="modal fade" id="assignModal" data-backdrop="static" role="dialog"
                                        aria-labelledby="assignModalTitle" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header text-center">
                                                    <h5 class="modal-title " id="exampleModalLongTitle"><?php echo e($ticket->Title); ?>

                                                    </h5>
                                                    <button type="button" class="close" id="myModalClose"
                                                        data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <form id="assignForm" name="assignForm">
                                                    <div class="modal-body">
                                                        <?php echo csrf_field(); ?>
                                                        <textarea rows="3" class="form-control"
                                                            disabled><?php echo e($ticket->Description); ?></textarea>
                                                        <input type="hidden" name="TicketID" value="<?php echo e($ticket->Id); ?>">
                                                        <span id="errors"></span>
                                                        <div class="row">
                                                            <div class="col-md-7">
                                                                
                                                                <div class="form-group">
                                                                    <label for="AssignedTo">Assign <span class="text-danger">*</span>
                                                                    </label>
                                                                    <select name="AssignedTo" id="AssignedTo"
                                                                        class="form-control <?php $__errorArgs = ['AssignedTo'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                                                        required>
                                                                        <option value="" selected disabled>Select User</option>
                                                                        <?php $__empty_1 = true; $__currentLoopData = \App\Models\User::where('Role', 1)->get(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $admin_user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                                                            <?php if($ticket->assignedTo != null): ?>
                                                                                <?php if(($ticket->assignedTo->ID == $admin_user->ID)): ?>
                                                                                    <?php continue; ?>
                                                                                <?php endif; ?>
                                                                            <?php endif; ?>
                                                                            <?php if(($admin_user->ID == Auth::user()->ID)): ?>
                                                                                <?php continue; ?>
                                                                            <?php endif; ?>
                                                                            <option value="<?php echo e($admin_user->ID); ?>">
                                                                                <?php echo e($admin_user->full_name); ?></option>
                                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                                                            <option value="" disabled>No Admins</option>
                                                                        <?php endif; ?>
                                                                    </select>
                                                                    <?php $__errorArgs = ['AssignedTo'];
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
                                                            <div class="col-md-3 text-right pt-3">
                                                                <!-- Default checkbox -->
                                                                <label for=""></label>
                                                                <div class="form-check">
                                                                    <input
                                                                    class="form-check-input"
                                                                    type="checkbox"
                                                                    value="<?php echo e(Auth::user()->ID); ?>"
                                                                    id="selfAssignCheck"
                                                                    />
                                                                    <label class="form-check-label" for="selfAssignCheck">
                                                                    Self Assign
                                                                    </label>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="form-group">
                                                            <label for="Comment">Comment <span
                                                                    class="text-danger">*</span></label>
                                                            <textarea name="Comment" id="Comment" cols="" rows=""
                                                                class="form-control <?php $__errorArgs = ['Comment'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                                                required><?php echo e(old('Comment')); ?></textarea>
                                                            <?php $__errorArgs = ['Comment'];
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
                                                    <div class="modal-footer">
                                                        <a href="javascript:history.back()" class="btn btn-outline-info btn-sm"
                                                            id="homeLink"><i class="fas fa-home"></i> Home</a>
                                                        <button type="button" class="btn btn-outline-danger btn-sm"
                                                            id="myModalClose2" data-dismiss="modal"><i
                                                                class="fas fa-times"></i>
                                                            Close</button>
                                                        <button type="submit" class="btn btn-primary"
                                                            id="saveBtn">Assign</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="title_desc" id="title_desc">
                                    <?php if($ticket->Status != 2 && $ticket->Status != 3): ?>
                                        <a class="float-right btn btn-sm btn-warning" href="#" data-toggle="modal" data-target="#editTicketModal"><i class="fa fa-sm fa-edit"></i> Edit Ticket</a>
                                    <?php endif; ?>
                                    <h5 data-toggle="tooltip" title="Ticket Title">Title</h5>
                                        <span class="h6"><?php echo ucfirst($ticket->Title); ?></span>
                                    <h5>Description</h5>
                                    <p> <?php echo $ticket->Description; ?></p>
                                </div>
                                <div class="modal fade" id="editTicketModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                      <div class="modal-content">
                                        <div class="modal-header">
                                          <h5 class="modal-title" id="editTicketModalLabel">EDIT TICKET</h5>
                                          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                          </button>
                                        </div>
                                        <div class="modal-body" id="edit_modal_body">
                                         <form action="" method="post" id="update_ticket_form">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('PUT'); ?>
                                            <input type="hidden" name="TicketID" value="<?php echo e($ticket->Id); ?>">
                                            <div class="form-group">
                                                <label for="Title" class="form-label">Title</label>
                                                <input type="text" class="form-control form-control-sm" name="Title" value="<?php echo e(old('Title', $ticket->Title)); ?>">
                                            </div>
                                            <div class="form-group">
                                                <label for="Description" class="form-label">Description</label>
                                                <textarea name="Description" id="Description"class="form-control"><?php echo e(old('Description', $ticket->Description)); ?></textarea>
                                            </div>
                                        </form>
                                        </div>
                                        <div class="modal-footer">
                                          <button type="button" class="btn btn-secondary" data-dismiss="modal" id="close">Close</button>
                                          <button type="button" class="btn btn-primary" id="ticket_update">Save changes</button>
                                        </div>
                                      </div>
                                    </div>
                                  </div>

                                <table class="table table-sm">
                                    <thead>
                                        <th>Solution</th>
                                        <th>Category</th>
                                        <th>Sub-Category</th>
                                        <th>Priority</th>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><?php echo e($ticket->SolutionName); ?></td>
                                            <td><?php echo e($ticket->ModuleName); ?></td>
                                            <td><?php echo e($ticket->SubModuleName); ?></td>
                                            <td><?php echo e($ticket->Priority); ?></td>
                                        </tr>
                                    </tbody>
                                </table>
                                
                                <div class="row">
                                    <div class="col-md-12">
                                        <h5 class="float-left">Attachment(s)</h5>
                                        <span class="float-right">
                                            <?php if($ticket->Status != 3 && $ticket->Status != 5): ?>
                                                <form name="attachment_upload_form" id="attachment_upload_form"
                                                    enctype="multipart/form-data">
                                                    <?php echo csrf_field(); ?>
                                                    <input type="hidden" name="TicketID" value="<?php echo e($ticket->Id); ?>">
                                                    <label for="upload">
                                                        <span class="fas fa-file-upload" aria-hidden="true"></span>
                                                        <span id="Upload-text">Upload</span>
                                                        <input type="file" id="upload" name="attachments[]"
                                                            style="display:none">
                                                    </label>
                                                </form>
                                            <?php endif; ?>
                                        </span>
                                        <?php if(count($ticket->attachments->toArray()) > 0): ?>
                                            <table class="table table-sm clearfix">
                                                <thead>
                                                    <th>CreatedBy</th>
                                                    <th>File Name</th>
                                                    <th>Opetation</th>
                                                </thead>
                                                <tbody>
                                                    <?php $__currentLoopData = $ticket->attachments->toArray(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attachment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <tr>
                                                            <td><?php echo e($user->where('ID', $attachment['CreatedBy'])->first()->full_name); ?>

                                                            </td>
                                                            <td>
                                                                <a href="<?php echo e(URL::signedRoute('attachment.download', $attachment['ID'])); ?>"
                                                                    target="_blank"><i class="fas fa-paperclip"></i>
                                                                    <?php echo e($attachment['FileName']); ?> &nbsp;
                                                                    
                                                                </a>
                                                            </td>
                                                            <td>
                                                                <?php if($ticket->Status != 3 && $ticket->Status != 5): ?>

                                                                    <?php if(Auth::user()->ID == $attachment['CreatedBy']): ?>
                                                                        <input type="hidden" name="del_attachment"
                                                                            id="del_attachment"
                                                                            value="<?php echo e($attachment['ID']); ?>">
                                                                        <button
                                                                            class="btn btn-xs btn-outline-danger delete_attachment">
                                                                            <i class="fas fa-x fa-times"></i> Remove
                                                                        </button>
                                                                    <?php else: ?>
                                                                        <button
                                                                            class="btn btn-xs btn-outline-danger delete_attachment disabled">
                                                                            <i class="fas fa-x fa-times"></i> Removes
                                                                        </button>
                                                                    <?php endif; ?>
                                                                <?php else: ?>
                                                                    Locked
                                                                <?php endif; ?>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </tbody>
                                            </table>
                                        <?php else: ?>
                                            <div class="text-info clearfix">
                                                <i class="fas fa-paperclip"></i> No attachment(s)
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 border-bottom border-success">
                                <div id="app">
                                    <div class="card card-default direct-chat">
                                        <div class="card-header">
                                            <ul class="nav nav-pills">
                                                <li class="nav-item"><a class="nav-link active" href="#chat"
                                                        data-toggle="tab">Chat</a></li>
                                                <li class="nav-item"><a class="nav-link" href="#track"
                                                        data-toggle="tab">Track</a></li>
                                                <li class="nav-item"><a class="nav-link" href="#all"
                                                        data-toggle="tab">All</a></li>
                                            </ul>
                                        </div>
                                        <div>
                                            <div class="tab-content">
                                                <div class="active tab-pane" id="chat">
                                                    <div class="card-body"
                                                        style="max-height: 300px; overflow-y: auto;">
                                                        <chat-messages :messages="messages" :data="<?php echo e($ticket); ?>"
                                                            :user="<?php echo e(Auth::user()); ?>">
                                                        </chat-messages>
                                                    </div>
                                                    <div class="card-footer">
                                                        <?php if($ticket->Status == 3 || $ticket->Status == 5): ?>
                                                            <em><small>Chats closed</small></em>
                                                        <?php else: ?>
                                                            <chat-form v-on:messagesent="addMessage"
                                                                :user="<?php echo e(Auth::user()); ?>" :data="<?php echo e($ticket); ?>">
                                                            </chat-form>
                                                        <?php endif; ?>

                                                    </div>
                                                </div>
                                                <div class="tab-pane" id="track">
                                                    <div class="card-body"
                                                        style="max-height: 300px; overflow-y: auto;">
                                                        <div class="timeline timeline-inverse pt-2">
                                                            <?php $__empty_1 = true; $__currentLoopData = $ticket->messages->where('Type','<>', 'Chat'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                                            
                                                                <div>
                                                                    <i class="fas fa-fw fa-ticket-alt bg-purple"
                                                                        title="Ticket"></i>

                                                                    <div class="timeline-item">
                                                                        <span class="time">
                                                                            <i class="far fa-clock"></i>
                                                                            <?php echo e($item->CreatedOn->diffForHumans()); ?>

                                                                        </span>

                                                                        <h3 class="timeline-header small p-1">
                                                                            <?php echo $item->Comment; ?> <br><small
                                                                                class="text-bold">
                                                                            <?php if($item->Comment == 'Ticket Created'): ?> By <?php elseif($item->Comment == "Ticket Assigned"): ?> To <?php endif; ?>
                                                                              <?php if($item->Type == 'Re-Assign'): ?>
                                                                              <?php echo e(__($item->messageNewUser->full_name)); ?>

                                                                              <?php else: ?>
                                                                              <?php echo e(__($item->user->full_name)); ?>

                                                                              <?php endif; ?>  </small>

                                                                        </h3>
                                                                    </div>
                                                                </div>
                                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                    <div class="card-footer">
                                                        <em>Ticket Track as of <?php echo e($ticket->CreatedOn); ?></em>
                                                    </div>
                                                </div>
                                                <div class="tab-pane" id="all">
                                                    <div class="card-body"
                                                        style="max-height: 300px; overflow-y: auto;">
                                                        <div class="timeline timeline-inverse  pt-2">
                                                            <?php $__empty_1 = true; $__currentLoopData = $ticket->messages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                                                <div>
                                                                    <?php if($item->Type == 'Chat'): ?>
                                                                        <i class="fas fa-envelope bg-primary"
                                                                            title="Chat"></i>
                                                                    <?php else: ?>
                                                                        <i class="fas fa-fw fa-ticket-alt bg-purple"
                                                                            title="Ticket"></i>
                                                                    <?php endif; ?>
                                                                    <div class="timeline-item">
                                                                        <span class="time"><i
                                                                                class="far fa-clock"></i><?php echo e($item->CreatedOn->diffForHumans()); ?></span>
                                                                        <h3 class="timeline-header small p-1">
                                                                            <?php echo $item->Comment; ?> <br><small
                                                                                class="text-bold"><?php if($item->Comment == 'Ticket Created'): ?> By <?php elseif($item->Comment == "Ticket Assigned"): ?> To <?php else: ?> By <?php endif; ?>
                                                                                <?php if($item->Type == 'Re-Assign'): ?>
                                                                              <?php echo e(__($item->messageNewUser->full_name)); ?>

                                                                              <?php else: ?>
                                                                              <?php echo e(__($item->user->full_name)); ?>

                                                                              <?php endif; ?> </small>
                                                                        </h3>
                                                                    </div>
                                                                </div>
                                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>

                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                    <div class="card-footer">
                                                        <em>Ticket Timeline as of <?php echo e($ticket->CreatedOn); ?></em>
                                                     </div>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('css'); ?>
    <style>
        .bs4-order-tracking {
            /* margin-bottom: 30px; */
            overflow: hidden;
            color: #878788;
            padding-left: 0px;
            /* margin-top: 30px */
        }

        .bs4-order-tracking li {
            list-style-type: none;
            font-size: 13px;
            width: 20%;
            float: left;
            position: relative;
            font-weight: 400;
            color: #878788;
            text-align: center
        }

        .bs4-order-tracking li:first-child:before {
            margin-left: 15px !important;
            padding-left: 11px !important;
            text-align: left !important
        }

        .bs4-order-tracking li:last-child:before {
            margin-right: 5px !important;
            padding-right: 11px !important;
            text-align: right !important
        }

        .bs4-order-tracking li>div {
            color: #fff;
            width: 29px;
            text-align: center;
            line-height: 29px;
            display: block;
            font-size: 12px;
            background: #878788;
            border-radius: 50%;
            margin: auto
        }

        .bs4-order-tracking li:after {
            content: '';
            width: 150%;
            height: 2px;
            background: #878788;
            position: absolute;
            left: 0%;
            right: 0%;
            top: 15px;
            z-index: -1
        }

        .bs4-order-tracking li:first-child:after {
            left: 50%
        }

        .bs4-order-tracking li:last-child:after {
            left: 0% !important;
            width: 0% !important
        }

        .bs4-order-tracking li.active {
            font-weight: bold;
            color: #2b8b3b
        }

        .bs4-order-tracking li.active>div {
            background: #2b8b3b
        }

        .bs4-order-tracking li.active:after {
            background: #2b8b3b
        }

        .card-timeline {
            background-color: #fff;
            z-index: 0
        }

    </style>
    <link href="//cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.0/jqueryui-editable/css/jqueryui-editable.css" rel="stylesheet"/>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.0.0-alpha.6/css/bootstrap.min.css">
<?php $__env->stopSection(); ?>

<?php $__env->startSection('js'); ?>
    <script>
        $(function() {
            const app = new Vue({
                el: '#app',

                data: {
                    messages: [],
                },

                created() {
                    this.fetchMessages();
                    /* Echo.private('chat')
                    //     .listen('MessageSent', (e) => {
                    //         this.messages.push({
                    //             message: e.Message,
                    //             user: e.user,
                    //             data: e.data
                    //         });
                    //     });*/
                },

                methods: {
                    fetchMessages() {
                        axios.get('/messages/').then(response => {
                            this.messages = response.data;
                            /* console.log( response);*/
                        });
                    },

                    addMessage(message) {
                        console.log(message);
                        var org_text = message.Comment;
                        console.log(org_text);
                        var new_text = org_text.replace(/#(\w+\-?\w+)/g,
                            '<a target="_blank" href="http://jira.aurionprosolutions.com:8080/browse/$1">#$1</a>'
                            );
                        message.Comment = new_text;
                        this.messages.push(message);
                        /*console.log(this.messages);*/
                        axios.post('/messages', message).then(response => {
                            /*console.log(response);*/
                        });
                    }
                }
            });
        });
    </script>
    <script>
        $(function() {
            /* console.log(($('#assignedTo').text()== "Not Assigned") && ($('.userId').attr('id') == 1));*/
            if (($('#assignedTo').text() == "Not Assigned") && ($('.userId').attr('id') == 1)) {
                toastr.info("Assign Ticket To Proceed");
                $("#modalShowButton").click();
                $('#homeLink').show();
                $('#myModalClose').hide();
                $('#myModalClose2').hide();
            }

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            /* on checkbox checked*/
            $('input:checkbox').change(function(){
                    if ($(this).is(':checked')) {
                        $('#Comment').val('Self Assingned');
                        var optionValue = $(this).val();
                        $('#AssignedTo').append(`<option value="${optionValue}" selected>Self Assigned</option>`);
                        $('#saveBtn').click();
                        /*Self assign*/

                    }
                });

            $('#saveBtn').click(function(e) {
                e.preventDefault();
                $(this).html('Sending..');
                $("form").append("<input type='hidden' name='status' value="+$('#status').val()+">");
                /* console.log($('#assignForm'));*/

               $.ajax({
                    data: $('#assignForm').serialize(),
                    url: "<?php echo e(route('tickets.assign_ticket_user')); ?>",
                    type: "POST",
                    dataType: 'json',
                    success: function(data) {
                        $('#assignForm').trigger("reset");
                        $("#myModalClose").click();
                        $('#assignedTo').html(data.assignedTo.full_name);
                        toastr.success(data.message);
                        $('#saveBtn').html('Assign');
                        location.reload();
                        $('#modalShowButton').html('Re-Assign');
                        $('#myModalClose').show();
                        $('#myModalClose2').show();
                    },
                    error: function(xhr, status, error) {
                        $.each(xhr.responseJSON.errors, function(key, item) {
                            $("#errors").append("<li class='alert alert-danger'>" + item + "</li>");
                        });
                        $('#saveBtn').removeClass('disabled');

                        $('#saveBtn').html('Assign');

                    }
                });/* */
            });

            $(".btn-add-more").click(function() {
                var html = $(".clone").html();
                $(".img_div").after(html);
            });

            $("body").on("click", ".btn-remove", function() {
                $(this).parents(".control-group").remove();
            });

            $('.delete_attachment').on('click', function() {
                var attachmentID = $('#del_attachment').val();
                Swal.fire({
                    title: 'Are you sure?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, Delete !!',
                    allowOutsideClick: false,
                    allowEscapeKey: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "<?php echo e(route('attachment.destroy')); ?>",
                            type: 'DELETE',
                            dataType: 'json',
                            data: {
                                "_token": $("meta[name='csrf-token']").attr("content"),
                                ID: attachmentID,
                            },
                            success: function(success) {

                                Swal.fire(
                                    'Deleted!',
                                    'Attachment Deleted !!',
                                    'success'
                                );

                                location.reload();
                            },
                            error: function(error) {
                                Swal.fire(
                                    'Oops!',
                                    'Something went wrong !!',
                                    'warning'
                                );
                                /*console.log(error);*/
                            }
                        });

                    } else {}
                });
            });
            $('#upload').on('change', function() {
                $('#Upload-text').html('Uploading <i class="fas fa-spinner"></i>');
                var form = document.forms.namedItem("attachment_upload_form");
                var formdata = new FormData(form);
                $.ajax({
                    async: true,
                    type: "POST",
                    dataType: "json",
                    contentType: false,
                    url: "<?php echo e(route('attachment.upload')); ?>",
                    data: formdata,
                    processData: false,
                    success: function(data) {
                        location.reload();
                    },
                    error: function(error) {
                        alert(
                            'Only pdf,jpg,png,jpeg,csv,doc,docx,txt,xlx,xls,xlsx Allowed. With a max size of 2MB'
                        );
                        location.reload();
                    }

                });
            });
            $('#changeTicketStatus').change(function() {
                var statusText = $('#changeTicketStatus option:selected').text();
                Swal.fire({
                    title: 'Are you sure?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, Mark as ' + statusText + '!',
                    allowOutsideClick: false
                }).then((result1) => {
                    if (result1.isConfirmed) {
                /*Swal.fire({
                    title: "Status Change to " + statusText,
                    text: "Comment (Required)",
                    input: 'textarea',
                    showCancelButton: true,
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    inputValidator: function(value) {
                        if (!value) {
                            return 'You need to write something!'
                        }
                    }
                }).then((result2) => {
                    if (result2.value) {*/
                        const Toast = Swal.mixin({
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000,
                            timerProgressBar: true,
                            didOpen: function(toast) {
                                toast.addEventListener('mouseenter', Swal.stopTimer);
                                toast.addEventListener('mouseleave', Swal.resumeTimer);
                            }
                        });
                        Toast.fire({
                            icon: 'success',
                            title: 'Give me a Second !!',
                            timer: 5000
                            });
                        $.ajax({
                            url: "<?php echo e(route('tickets.update_status')); ?>",
                            method: 'GET',
                            dataType: 'json',
                            data:  {
                                ID: $('#ticketId').val(),
                                Status: $('#changeTicketStatus option:selected').val(),
                                /*Message: "result2.value"*/
                                Message: "Ticket " + statusText
                            },
                            success: function(response) {
                                /* window.location.href = '/home';*/
                                Swal.fire({
                                    icon: response.status,
                                    title : 'Ticket '+$('#changeTicketStatus option:selected').text(),
                                    text: response.message,
                                    type: response.status,
                                    showConfirmButton: true,
                                    allowOutsideClick: false,
                                    allowEscapeKey: false,
                                }).then((result1) => {
                                        if (result1.isConfirmed) {
                                            location.reload();
                                        }
                                    });
                            },
                            error: function(error) {
                                console.log(error);
                            }
                        });
                        /* window.location.href = '/home';
                        location.reload();*/
                    } else {
                        /**/location.reload();
                    }
                });

                /* } else {
                        location.reload();
                    }
                });*/
            });
        });
    </script>
    <script>
        $.fn.poshytip = {
            defaults: null
        }
    </script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.0/jquery-editable/js/jquery-editable-poshytip.min.js"></script>
    
    <script>
        $(function() {
            $.fn.editable.defaults.mode = 'inline';

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
                }
            });

            $('#ticket_update').click(function(e){

                console.log('here');

                $.ajax({
                    url: `/tickets/<?php echo e($ticket->Id); ?>`,
                    method: 'POST',
                    dataType: 'json',
                    data: $('#update_ticket_form').serializeArray(),
                    success: function(response){
                        $('#close').click();

                        toastr.success(response.message);
                        location.reload();
                        console.log(response);

                    },
                    error: function(error){
                        console.log(error);
                }});
            });
        });
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('adminlte::page', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\inetpub\wwwroot\fit_service_desk\resources\views/tickets/picked_ticket.blade.php ENDPATH**/ ?>