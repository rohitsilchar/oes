<?php 
  $enrolments = $this->db->where('course_id', $course_details['id'])->get('enrol')->result_array();
  $lessons = $this->crud_model->get_lessons('course', $course_details['id']);
  $total_lesson = $lessons->num_rows();
  $quizzes = $this->db->order_by('order', 'asc')->get_where('lesson', ['course_id' => $course_details['id'], 'lesson_type' => 'quiz'])->result_array();
  $total_quizzes = count($quizzes);
?>

<div class="row mb-3 align-items-center">
  <div class="col-md-6 col-sm-12">
    <h5 class="my-0 font-15 text-dark font-weight-bold">
      <i class="mdi mdi-account-group mr-1 text-primary"></i>
      <?php echo get_phrase('Enrolled Pharmacists'); ?>
      <span class="badge badge-primary-lighten ml-1"><?php echo count($enrolments); ?></span>
    </h5>
  </div>
  <div class="col-md-6 col-sm-12 text-md-right mt-2 mt-md-0">
    <div class="btn-group" role="group">
      <a href="<?php echo site_url('admin/export_student_progress_excel/' . $course_details['id']); ?>" class="btn btn-success btn-sm font-13 shadow-sm" data-toggle="tooltip" title="<?php echo get_phrase('Download Excel spreadsheet for all pharmacists'); ?>">
        <i class="mdi mdi-file-excel mr-1"></i><?php echo get_phrase('Export to Excel'); ?>
      </a>
      <a href="<?php echo site_url('admin/export_student_progress_csv/' . $course_details['id']); ?>" class="btn btn-outline-secondary btn-sm font-13" data-toggle="tooltip" title="<?php echo get_phrase('Download CSV file for all pharmacists'); ?>">
        <i class="mdi mdi-file-delimited mr-1"></i><?php echo get_phrase('Export to CSV'); ?>
      </a>
    </div>
  </div>
</div>

<div class="table-responsive">
  <table class="studentAcademicProgress table table-striped table-centered mb-4">
    <thead>
      <tr>
        <th><?php echo get_phrase('Pharmacist'); ?></th>
        <th><?php echo get_phrase('Date'); ?></th>
        <th><?php echo get_phrase('Progress'); ?></th>
        <th><?php echo get_phrase('Quiz Result'); ?></th>
        <th class="text-center"><?php echo get_phrase('Actions'); ?></th>
      </tr> 
    </thead>
    <tbody>
      <?php if (count($enrolments) == 0): ?>
        <tr>
          <td colspan="5" class="text-center py-4 text-muted">
            <i class="mdi mdi-account-off-outline font-24 d-block mb-1"></i>
            <?php echo get_phrase('No pharmacists enrolled in this course yet'); ?>.
          </td>
        </tr>
      <?php endif; ?>
      <?php
      foreach($enrolments as $enrolment):
        $student = $this->user_model->get_all_user($enrolment['user_id'])->row_array();
        if (empty($student)) continue;
        $student_id = $enrolment['user_id'];
        $watch_history = $this->db->where('course_id', $course_details['id'])->where('student_id', $student_id)->get('watch_histories')->row_array();
        $completed_lesson_arr = isset($watch_history['completed_lesson']) ? json_decode($watch_history['completed_lesson'], true) : [];
        $completed_lesson = is_array($completed_lesson_arr) ? $completed_lesson_arr:[];

        $date_updated = isset($watch_history['date_updated']) ? date('d M Y, H:i a', $watch_history['date_updated']) : get_phrase('Not started yet');
        $completed_date = isset($watch_history['completed_date']) ? date('d M Y', $watch_history['completed_date']) : get_phrase('Not completed yet');
        $course_progress = isset($watch_history['course_progress']) ? $watch_history['course_progress'] : 0;
        ?>
        <tr>
          <td>
            <p class="my-0 font-weight-semibold"><?php echo htmlspecialchars($student['first_name'].' '.$student['last_name']); ?></p>
            <span class="badge badge-light"><?php echo htmlspecialchars($student['email']); ?></span>
          </td>
          <td>
            <p class="my-0"><b><?php echo get_phrase('Enrolled from'); ?>-</b> <?php echo date('d M Y', $enrolment['date_added']); ?></p>

            <p class="my-0"><b><?php echo get_phrase('last seen on'); ?>-</b> <?php echo $date_updated; ?></p>

            <p class="my-0"><b><?php echo get_phrase('Completed on'); ?>-</b> <?php echo $completed_date; ?></p>
          </td>
          <td>
            <div class="progress" style="height: 16px;">
              <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $course_progress; ?>%; font-size: 11px; font-weight: 600;" aria-valuenow="<?php echo $course_progress; ?>" aria-valuemin="0" aria-valuemax="100"><?php echo $course_progress; ?>%</div>
            </div>
            <p class="my-0 mt-1 font-12">- <?php echo get_phrase('Completed lesson').' '.count($completed_lesson).' '.get_phrase('out of').' '.$total_lesson; ?></p>

            <?php
              $total_watched_duration = 0; //seconds
              $watched_durations = $this->db->get_where('watched_duration', ['watched_student_id' => $student_id, 'watched_course_id' => $course_details['id']]);
              foreach($watched_durations->result_array() as $watched_duration){
                $total_watched_duration += count(json_decode($watched_duration['watched_counter'], true))*5;
              }
            ?>

            <p class="my-0 font-12">- <?php echo get_phrase('Watched duration').'- <b>'.seconds_to_time_format($total_watched_duration); ?></b></p>
          </td>
          <!-- Quiz Result Column -->
          <td>
            <?php if ($total_quizzes == 0): ?>
              <span class="badge badge-secondary-lighten py-1 px-2 text-muted">
                <i class="mdi mdi-information-outline mr-1"></i><?php echo get_phrase('No quiz'); ?>
              </span>
            <?php elseif ($total_quizzes == 1): ?>
              <?php
                $quiz = $quizzes[0];
                $quiz_info = json_decode($quiz['attachment'] ?? '{}', true) ?: [];
                $total_marks = !empty($quiz_info['total_marks']) ? floatval($quiz_info['total_marks']) : 0;
                $pass_mark   = isset($quiz_info['pass_mark']) && $quiz_info['pass_mark'] !== '' ? floatval($quiz_info['pass_mark']) : 0;
                if ($total_marks == 0) {
                    $total_marks = $this->db->where('quiz_id', $quiz['id'])->count_all_results('question');
                }

                $quiz_results = $this->db->order_by('quiz_result_id', 'desc')->where('quiz_id', $quiz['id'])->where('user_id', $student_id)->get('quiz_results');
                $total_attempts = $quiz_results->num_rows();
              ?>
              <?php if ($total_attempts == 0): ?>
                <span class="badge badge-warning-lighten py-1 px-2 font-12 font-weight-bold">
                  <i class="far fa-clock mr-1"></i><?php echo get_phrase('Not attempted'); ?>
                </span>
              <?php else: ?>
                <?php
                  $latest_res = $quiz_results->row_array();
                  $obtained = floatval($latest_res['total_obtained_marks']);
                  $percent = ($total_marks > 0) ? round(($obtained / $total_marks) * 100) : 0;
                  $is_passed = ($pass_mark > 0) ? ($obtained >= $pass_mark) : ($percent >= 50);
                ?>
                <div class="d-flex align-items-center mb-1">
                  <?php if ($is_passed): ?>
                    <span class="badge badge-success-lighten py-1 px-2 font-12 font-weight-bold">
                      <i class="mdi mdi-check-circle mr-1"></i><?php echo get_phrase('Passed'); ?>
                    </span>
                  <?php else: ?>
                    <span class="badge badge-danger-lighten py-1 px-2 font-12 font-weight-bold">
                      <i class="mdi mdi-close-circle mr-1"></i><?php echo get_phrase('Failed'); ?>
                    </span>
                  <?php endif; ?>
                  <span class="ml-2 font-weight-bold <?php echo $is_passed ? 'text-success' : 'text-danger'; ?>">
                    <?php echo $obtained; ?> / <?php echo $total_marks; ?> (<?php echo $percent; ?>%)
                  </span>
                </div>
                <?php if ($pass_mark > 0): ?>
                  <p class="my-0 text-muted font-11">
                    <b><?php echo get_phrase('Pass mark'); ?>:</b> <?php echo $pass_mark; ?> / <?php echo $total_marks; ?>
                  </p>
                <?php endif; ?>
                <p class="my-0 text-muted font-11">
                  <b><?php echo get_phrase('Attempts'); ?>:</b> <?php echo $total_attempts; ?>
                  <a href="javascript:;" onclick="showLargeModal('<?php echo site_url('admin/student_academic_quiz_result/'.$course_details['id'].'/'.$student_id); ?>', '<?php echo get_phrase('Quiz results'); ?>')" class="ml-1 text-primary font-weight-bold">
                    <i class="mdi mdi-eye mr-1"></i><?php echo get_phrase('details'); ?>
                  </a>
                </p>
              <?php endif; ?>
            <?php else: ?>
              <?php
                $attempted_quizzes = 0;
                $passed_quizzes = 0;
                $quiz_summaries = [];

                foreach ($quizzes as $quiz) {
                    $quiz_info = json_decode($quiz['attachment'] ?? '{}', true) ?: [];
                    $total_marks = !empty($quiz_info['total_marks']) ? floatval($quiz_info['total_marks']) : 0;
                    $pass_mark   = isset($quiz_info['pass_mark']) && $quiz_info['pass_mark'] !== '' ? floatval($quiz_info['pass_mark']) : 0;
                    if ($total_marks == 0) {
                        $total_marks = $this->db->where('quiz_id', $quiz['id'])->count_all_results('question');
                    }

                    $quiz_results = $this->db->order_by('quiz_result_id', 'desc')->where('quiz_id', $quiz['id'])->where('user_id', $student_id)->get('quiz_results');
                    $attempts = $quiz_results->num_rows();

                    if ($attempts > 0) {
                        $attempted_quizzes++;
                        $latest_res = $quiz_results->row_array();
                        $obtained = floatval($latest_res['total_obtained_marks']);
                        $percent = ($total_marks > 0) ? round(($obtained / $total_marks) * 100) : 0;
                        $is_passed = ($pass_mark > 0) ? ($obtained >= $pass_mark) : ($percent >= 50);
                        if ($is_passed) $passed_quizzes++;
                        $quiz_summaries[] = [
                            'title' => $quiz['title'],
                            'obtained' => $obtained,
                            'total' => $total_marks,
                            'percent' => $percent,
                            'passed' => $is_passed,
                            'attempts' => $attempts
                        ];
                    } else {
                        $quiz_summaries[] = [
                            'title' => $quiz['title'],
                            'not_attempted' => true
                        ];
                    }
                }
              ?>
              <?php if ($attempted_quizzes == 0): ?>
                <span class="badge badge-warning-lighten py-1 px-2 font-12 font-weight-bold">
                  <i class="far fa-clock mr-1"></i><?php echo get_phrase('Not attempted'); ?> (0/<?php echo $total_quizzes; ?>)
                </span>
              <?php else: ?>
                <div class="mb-1">
                  <?php if ($passed_quizzes == $total_quizzes): ?>
                    <span class="badge badge-success-lighten py-1 px-2 font-12 font-weight-bold">
                      <i class="mdi mdi-check-all mr-1"></i><?php echo get_phrase('All Passed'); ?> (<?php echo $passed_quizzes; ?>/<?php echo $total_quizzes; ?>)
                    </span>
                  <?php else: ?>
                    <span class="badge badge-danger-lighten py-1 px-2 font-12 font-weight-bold">
                      <i class="mdi mdi-alert-circle mr-1"></i><?php echo $passed_quizzes; ?>/<?php echo $total_quizzes; ?> <?php echo get_phrase('Passed'); ?>
                    </span>
                  <?php endif; ?>
                </div>
                <?php foreach ($quiz_summaries as $idx => $qs): ?>
                  <p class="my-0 font-11 text-truncate" style="max-width: 220px;" title="<?php echo htmlspecialchars($qs['title']); ?>">
                    <b>Q<?php echo ($idx+1); ?>:</b>
                    <?php if (!empty($qs['not_attempted'])): ?>
                      <span class="text-muted"><?php echo get_phrase('not_taken'); ?></span>
                    <?php else: ?>
                      <span class="<?php echo $qs['passed'] ? 'text-success' : 'text-danger'; ?> font-weight-bold">
                        <?php echo $qs['obtained']; ?>/<?php echo $qs['total']; ?> (<?php echo $qs['percent']; ?>%)
                      </span>
                      <span class="badge <?php echo $qs['passed'] ? 'badge-success' : 'badge-danger'; ?> py-0 px-1 font-10">
                        <?php echo $qs['passed'] ? get_phrase('Pass') : get_phrase('Fail'); ?>
                      </span>
                    <?php endif; ?>
                  </p>
                <?php endforeach; ?>
                <p class="my-0 text-muted font-11 mt-1">
                  <a href="javascript:;" onclick="showLargeModal('<?php echo site_url('admin/student_academic_quiz_result/'.$course_details['id'].'/'.$student_id); ?>', '<?php echo get_phrase('Quiz results'); ?>')" class="text-primary font-weight-bold">
                    <i class="mdi mdi-eye mr-1"></i><?php echo get_phrase('details'); ?>
                  </a>
                </p>
              <?php endif; ?>
            <?php endif; ?>
          </td>
          <td class="text-center">
            <div class="btn-group" role="group" aria-label="Button group with nested dropdown">
              <a href="javascript:;" onclick="showLargeModal('<?php echo site_url('admin/student_academic_quiz_result/'.$course_details['id'].'/'.$student_id); ?>', '<?php echo get_phrase('Quiz results'); ?>')" class="btn btn-light cursor-pointer" data-toggle="tooltip" title="<?php echo get_phrase('Quiz results'); ?>"><i class="far fa-address-card"></i></a>

              <?php if(addon_status('certificate')): ?>
                <a href="<?php echo site_url('admin/student_certificate/'.$student_id.'/'.$course_details['id']); ?>" target="_blank" class="btn btn-light cursor-pointer" data-toggle="tooltip" title="<?php echo get_phrase('Certificate'); ?>">
                  <i class="fas fa-graduation-cap"></i>
                </a>
              <?php endif; ?>
            </div>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<script type="text/javascript">
  $('[data-toggle=tooltip]').tooltip();
</script>