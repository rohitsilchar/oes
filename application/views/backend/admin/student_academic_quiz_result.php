<?php 
$quizes = $this->db->order_by('order', 'asc')->get_where('lesson', ['course_id' => $course_details['id'], 'lesson_type' => 'quiz'])->result_array();
$student = $this->user_model->get_all_user($student_id)->row_array();
?>

<?php if (!empty($student)): ?>
<div class="card mb-3 border bg-light">
    <div class="card-body py-2 px-3">
        <div class="row align-items-center">
            <div class="col-sm-8">
                <h5 class="my-0 font-weight-bold text-dark">
                    <i class="mdi mdi-account-circle mr-1 text-primary"></i><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?>
                </h5>
                <span class="text-muted font-12"><?php echo htmlspecialchars($student['email']); ?></span>
            </div>
            <div class="col-sm-4 text-sm-right mt-2 mt-sm-0">
                <span class="badge badge-primary-lighten font-12 px-2 py-1">
                    <i class="mdi mdi-book-open-page-variant mr-1"></i><?php echo htmlspecialchars($course_details['title']); ?>
                </span>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if (empty($quizes)): ?>
    <div class="text-center py-4">
        <i class="mdi mdi-information-outline font-24 text-muted d-block mb-1"></i>
        <p class="text-muted font-14 mb-0"><?php echo get_phrase('no_quiz_found_in_this_course'); ?></p>
    </div>
<?php else: ?>
    <?php foreach($quizes as $key => $quiz): ?>
        <?php
            $attachment = json_decode($quiz['attachment'] ?? '{}', true) ?: [];
            $total_marks = !empty($attachment['total_marks']) ? floatval($attachment['total_marks']) : 0;
            $pass_mark = isset($attachment['pass_mark']) && $attachment['pass_mark'] !== '' ? floatval($attachment['pass_mark']) : 0;
            if ($total_marks == 0) {
                $total_marks = $this->db->where('quiz_id', $quiz['id'])->count_all_results('question');
            }

            $quiz_results = $this->db->order_by('quiz_result_id', 'desc')->where('quiz_id', $quiz['id'])->where('user_id', $student_id)->get('quiz_results');
            $total_attempts = $quiz_results->num_rows();
        ?>
        <div class="card border mb-3 shadow-none">
            <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center flex-wrap">
                <h5 class="my-1 font-14 font-weight-bold">
                    <span class="badge badge-info mr-1">Quiz #<?php echo ($key + 1); ?></span>
                    <?php echo htmlspecialchars_decode_($quiz['title']); ?>
                </h5>
                <div class="my-1">
                    <span class="badge badge-secondary-lighten mr-1 font-12">
                        <?php echo get_phrase('Total Marks'); ?>: <b><?php echo $total_marks; ?></b>
                    </span>
                    <?php if ($pass_mark > 0): ?>
                    <span class="badge badge-warning-lighten font-12">
                        <?php echo get_phrase('Pass mark'); ?>: <b><?php echo $pass_mark; ?></b>
                    </span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card-body p-3">
                <?php if ($total_attempts == 0): ?>
                    <div class="alert alert-warning py-2 px-3 font-13 mb-0">
                        <i class="mdi mdi-clock-outline mr-1"></i><?php echo get_phrase('The student has not attempted this quiz yet'); ?>.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm table-centered mb-0 font-13">
                            <thead class="thead-light">
                                <tr>
                                    <th>#</th>
                                    <th><?php echo get_phrase('Date & Time'); ?></th>
                                    <th><?php echo get_phrase('Obtained Marks'); ?></th>
                                    <th><?php echo get_phrase('Percentage'); ?></th>
                                    <th><?php echo get_phrase('Result'); ?></th>
                                    <th class="text-right"><?php echo get_phrase('Action'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($quiz_results->result_array() as $rIdx => $quiz_result): ?>
                                    <?php
                                        $obtained = floatval($quiz_result['total_obtained_marks']);
                                        $percent = ($total_marks > 0) ? round(($obtained / $total_marks) * 100) : 0;
                                        $is_passed = ($pass_mark > 0) ? ($obtained >= $pass_mark) : ($percent >= 50);
                                        $attempt_num = $total_attempts - $rIdx;
                                        $date_str = !empty($quiz_result['date_added']) ? date('d M Y, h:i A', $quiz_result['date_added']) : '-';
                                    ?>
                                    <tr>
                                        <td>
                                            <span class="badge badge-light">Attempt <?php echo $attempt_num; ?></span>
                                            <?php if ($rIdx == 0): ?>
                                                <span class="badge badge-primary-lighten font-10">Latest</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo $date_str; ?></td>
                                        <td>
                                            <b class="<?php echo $is_passed ? 'text-success' : 'text-danger'; ?>">
                                                <?php echo $obtained; ?> / <?php echo $total_marks; ?>
                                            </b>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center" style="gap: 6px;">
                                                <div class="progress flex-grow-1" style="height: 6px; width: 60px;">
                                                    <div class="progress-bar <?php echo $is_passed ? 'bg-success' : 'bg-danger'; ?>" style="width: <?php echo min(100, $percent); ?>%;"></div>
                                                </div>
                                                <span><?php echo $percent; ?>%</span>
                                            </div>
                                        </td>
                                        <td>
                                            <?php if ($is_passed): ?>
                                                <span class="badge badge-success"><i class="mdi mdi-check mr-1"></i><?php echo get_phrase('Passed'); ?></span>
                                            <?php else: ?>
                                                <span class="badge badge-danger"><i class="mdi mdi-close mr-1"></i><?php echo get_phrase('Failed'); ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-right">
                                            <a class="btn btn-outline-primary btn-sm py-0 px-2 font-12" href="<?php echo site_url('home/lesson/'.slugify($course_details['title']).'/'.$course_details['id'].'/'.$quiz['id'].'?student_id='.$student_id); ?>" target="_blank">
                                                <i class="mdi mdi-eye mr-1"></i><?php echo get_phrase('Answer sheet'); ?>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>