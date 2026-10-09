<?php
if (empty($result) && !empty($param2)) {
    $result = $this->db->get_where('quiz_results', ['quiz_result_id' => $param2])->row_array();
    if (!empty($result)) {
        $user = $this->db->get_where('users', ['id' => $result['user_id']])->row_array();
        $quiz = $this->db->get_where('lesson', ['id' => $result['quiz_id']])->row_array();
        $course = !empty($quiz['course_id']) ? $this->db->get_where('course', ['id' => $quiz['course_id']])->row_array() : [];
        $store = !empty($user['store_id']) ? $this->db->get_where('stores', ['id' => $user['store_id']])->row_array() : null;
        $questions = !empty($quiz['id']) ? $this->db->get_where('question', ['quiz_id' => $quiz['id']])->result_array() : [];
    }
}

if (empty($result)) {
    echo '<div class="alert alert-danger font-14">' . get_phrase('quiz_result_not_found') . '</div>';
    return;
}

$user_answers = !empty($result['user_answers']) ? json_decode($result['user_answers'], true) : [];
$correct_answers_summary = !empty($result['correct_answers']) ? json_decode($result['correct_answers'], true) : [];

$attachment = !empty($quiz['attachment']) ? json_decode($quiz['attachment'], true) : [];
$total_marks = !empty($attachment['total_marks']) ? floatval($attachment['total_marks']) : (count($questions) ?: 1);
$pass_mark = isset($attachment['pass_mark']) && $attachment['pass_mark'] !== '' ? floatval($attachment['pass_mark']) : 0;

$obtained = floatval($result['total_obtained_marks']);
$percentage = ($total_marks > 0) ? round(($obtained / $total_marks) * 100, 1) : 0;
$is_passed = ($pass_mark > 0) ? ($obtained >= $pass_mark) : ($percentage >= 50);

$user_image = $this->user_model->get_user_image_url($user['id']);
?>

<div class="row mb-3">
    <!-- Pharmacist & Store Overview -->
    <div class="col-md-7">
        <div class="card border mb-0 bg-light">
            <div class="card-body p-3">
                <div class="d-flex align-items-center">
                    <img src="<?php echo $user_image; ?>" alt="" height="54" width="54" class="rounded-circle img-thumbnail mr-3 shadow-sm">
                    <div>
                        <h5 class="my-0 font-16 font-weight-bold text-dark">
                            <?php echo html_escape($user['first_name'] . ' ' . $user['last_name']); ?>
                        </h5>
                        <?php if (!empty($user['employee_id'])): ?>
                            <small class="text-muted mr-2"><i class="mdi mdi-account-card-details mr-1"></i><?php echo html_escape($user['employee_id']); ?></small>
                        <?php endif; ?>
                        <small class="text-muted"><i class="mdi mdi-email-outline mr-1"></i><?php echo html_escape($user['email']); ?></small>
                        <div class="mt-1">
                            <?php if (!empty($store)): ?>
                                <span class="badge badge-outline-primary font-11">
                                    <i class="mdi mdi-store mr-1"></i><?php echo html_escape($store['store_name']); ?> (<?php echo html_escape($store['store_code']); ?>)
                                </span>
                            <?php else: ?>
                                <span class="badge badge-light border text-muted font-11"><?php echo get_phrase('no_store_assigned'); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Evaluation Score Card -->
    <div class="col-md-5 mt-2 mt-md-0">
        <div class="card border mb-0 <?php echo $is_passed ? 'border-success' : 'border-danger'; ?>">
            <div class="card-body p-3 text-center">
                <div class="mb-1">
                    <span class="badge <?php echo $is_passed ? 'badge-success' : 'badge-danger'; ?> font-14 px-3 py-1 text-uppercase font-weight-bold">
                        <i class="mdi <?php echo $is_passed ? 'mdi-check-decagram' : 'mdi-close-circle'; ?> mr-1"></i>
                        <?php echo $is_passed ? get_phrase('passed') : get_phrase('failed'); ?>
                    </span>
                </div>
                <h3 class="my-1 font-24 font-weight-bold <?php echo $is_passed ? 'text-success' : 'text-danger'; ?>">
                    <?php echo $obtained; ?> / <?php echo $total_marks; ?>
                    <span class="font-16 text-muted font-weight-normal">(<?php echo $percentage; ?>%)</span>
                </h3>
                <div class="font-12 text-muted">
                    <?php if ($pass_mark > 0): ?>
                        <span class="mr-2"><b><?php echo get_phrase('pass_mark'); ?>:</b> <?php echo $pass_mark; ?></span>
                    <?php endif; ?>
                    <span><i class="mdi mdi-clock-outline mr-1"></i><?php echo date('d M Y, h:i A', $result['date_added']); ?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Course & Quiz Header -->
<div class="alert alert-info py-2 px-3 font-13 mb-3 d-flex justify-content-between align-items-center flex-wrap">
    <div>
        <i class="mdi mdi-book-open-page-variant mr-1"></i> <b><?php echo get_phrase('course'); ?>:</b> <?php echo html_escape($course['title'] ?? '-'); ?>
    </div>
    <div>
        <i class="mdi mdi-help-circle-outline mr-1"></i> <b><?php echo get_phrase('quiz'); ?>:</b> <?php echo html_escape($quiz['title'] ?? '-'); ?>
    </div>
</div>

<!-- Question Breakdown -->
<h5 class="header-title mb-2 font-15">
    <i class="mdi mdi-format-list-checks text-primary mr-1"></i> <?php echo get_phrase('question_by_question_breakdown'); ?> (<?php echo count($questions); ?>)
</h5>

<?php if (empty($questions)): ?>
    <div class="alert alert-warning py-2"><?php echo get_phrase('no_questions_found_for_this_quiz'); ?></div>
<?php else: ?>
    <?php foreach ($questions as $qIdx => $q): ?>
        <?php
            $qNum = $qIdx + 1;
            $opts = !empty($q['options']) ? json_decode($q['options'], true) : [];
            $correct_arr = !empty($q['correct_answers']) ? json_decode($q['correct_answers'], true) : [];
            $submitted_ans = isset($user_answers[$q['id']]) ? (array)$user_answers[$q['id']] : [];
            
            // Check if user answer matches correct answers
            sort($correct_arr);
            $clean_submitted = $submitted_ans;
            sort($clean_submitted);
            $is_q_correct = ($clean_submitted == $correct_arr && !empty($clean_submitted));
        ?>
        <div class="card border mb-3 shadow-none">
            <div class="card-header py-2 d-flex justify-content-between align-items-center bg-white border-bottom">
                <span class="font-weight-bold font-13">
                    <span class="badge badge-secondary mr-2">Q<?php echo $qNum; ?></span>
                    <?php echo strip_tags($q['title']); ?>
                </span>
                <span>
                    <?php if ($is_q_correct): ?>
                        <span class="badge badge-success-lighten text-success font-12"><i class="mdi mdi-check-circle mr-1"></i><?php echo get_phrase('correct'); ?></span>
                    <?php else: ?>
                        <span class="badge badge-danger-lighten text-danger font-12"><i class="mdi mdi-close-circle mr-1"></i><?php echo get_phrase('incorrect'); ?></span>
                    <?php endif; ?>
                </span>
            </div>
            <div class="card-body p-3">
                <div class="row">
                    <?php foreach ($opts as $optIdx => $optText): ?>
                        <?php
                            $optKey = (string)($optIdx + 1);
                            $isSelected = in_array($optKey, $submitted_ans);
                            $isRight = in_array($optKey, $correct_arr);
                            
                            $badgeClass = 'bg-light border text-dark';
                            $icon = '';
                            if ($isSelected && $isRight) {
                                $badgeClass = 'bg-success-subtle border-success text-success font-weight-bold';
                                $icon = '<i class="mdi mdi-check-circle text-success ml-auto"></i>';
                            } elseif ($isSelected && !$isRight) {
                                $badgeClass = 'bg-danger-subtle border-danger text-danger font-weight-bold';
                                $icon = '<i class="mdi mdi-close-circle text-danger ml-auto"></i>';
                            } elseif (!$isSelected && $isRight) {
                                $badgeClass = 'bg-info-subtle border-info text-info';
                                $icon = '<span class="badge badge-info-lighten ml-auto font-10">' . get_phrase('correct_answer') . '</span>';
                            }
                        ?>
                        <div class="col-md-6 mb-2">
                            <div class="p-2 rounded d-flex align-items-center <?php echo $badgeClass; ?>" style="min-height: 42px;">
                                <span class="font-weight-bold mr-2"><?php echo chr(65 + $optIdx); ?>.</span>
                                <span><?php echo html_escape($optText); ?></span>
                                <?php echo $icon; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>
