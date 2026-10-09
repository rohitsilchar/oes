<form class="pb-5" action="<?php echo site_url('admin/quiz_questions/'.$param2.'/add'); ?>" method="post" id="mcq_form" onsubmit="event.preventDefault(); submitQuizQuestion();">

    <div class="form-group">
        <label for="question_title"><?php echo get_phrase('write_your_question'); ?><span class="text-danger">*</span></label>
        <textarea name="title" id="question_title" class="form-control"></textarea>
    </div>

    <div class="form-group">
        <label for="question_type"><?php echo get_phrase('question_type'); ?><span class="text-danger">*</span></label>
        <select class="form-control select2" data-toggle="select2" name="question_type" id="question_type" onchange="quiz_fields_type_wize(this)" required>
            <option value=""><?php echo get_phrase('select_question_type'); ?></option>
            <option value="multiple_choice"><?php echo get_phrase('multiple_choice'); ?></option>
            <option value="single_choice"><?php echo get_phrase('single_choice').' '.get_phrase('and').' true/false'; ?></option>
            <option value="fill_in_the_blank"><?php echo get_phrase('fill_in_the_blank'); ?></option>
        </select>
    </div>

    <div id="quiz_fields_type_wize"></div>

    <div class="text-center pt-3">
        <button class="btn btn-success" id="submitButton" type="button" onclick="submitQuizQuestion()" name="button"><?php echo get_phrase('submit_quiz_question'); ?></button>
    </div>
</form>

<script type="text/javascript">
    function quiz_fields_type_wize(e){
        var question_type = $('#question_type').val();
        if (!question_type) {
            $('#quiz_fields_type_wize').html('');
            return;
        }
        $.ajax({
            type: "POST",
            url: "<?php echo site_url('admin/quiz_fields_type_wize'); ?>",
            data: {question_type : question_type},
            success: function(response){
                jQuery('#quiz_fields_type_wize').html(response);
            }
        });
    }

    function syncSummernoteTitle() {
        if ($('#question_title').data('summernote') || typeof $('#question_title').summernote === 'function') {
            try {
                if ($('#question_title').summernote('isEmpty')) {
                    $('#question_title').val('');
                } else {
                    $('#question_title').val($('#question_title').summernote('code'));
                }
            } catch(e) {}
        }
    }

    function submitQuizQuestion() {
        // Sync Summernote editor to textarea before serializing
        syncSummernoteTitle();

        var title = ($('#question_title').val() || '').trim();
        var textOnly = title.replace(/<[^>]*>/g, '').trim();
        if (!title || (textOnly === '' && !title.includes('<img') && !title.includes('<svg'))) {
            error_notify('<?php echo get_phrase('question_title_can_not_be_empty'); ?>');
            return false;
        }

        var questionType = $('#question_type').val();
        if (!questionType) {
            error_notify('<?php echo get_phrase('select_question_type'); ?>');
            return false;
        }

        if (questionType === 'multiple_choice' || questionType === 'single_choice') {
            var numOptions = parseInt($('#number_of_options').val(), 10) || 0;
            if (numOptions <= 0) {
                error_notify('<?php echo get_phrase('no_options_can_be_blank_and_there_has_to_be_atleast_one_answer'); ?>');
                return false;
            }

            var optionInputs = $('input[name="options[]"]');
            if (optionInputs.length !== numOptions) {
                error_notify('<?php echo get_phrase('no_options_can_be_blank_and_there_has_to_be_atleast_one_answer'); ?>');
                return false;
            }

            var hasEmptyOption = false;
            optionInputs.each(function() {
                if ($(this).val().trim() === '') {
                    hasEmptyOption = true;
                }
            });
            if (hasEmptyOption) {
                error_notify('<?php echo get_phrase('no_options_can_be_blank_and_there_has_to_be_atleast_one_answer'); ?>');
                return false;
            }

            if ($('input[name="correct_answers[]"]:checked').length === 0) {
                error_notify('<?php echo get_phrase('correct_answer_can_not_be_empty'); ?>');
                return false;
            }
        } else if (questionType === 'fill_in_the_blank') {
            var answers = ($('#correct_answers').val() || '').trim();
            if (!answers) {
                error_notify('<?php echo get_phrase('correct_answer_can_not_be_empty'); ?>');
                return false;
            }
        }

        $('#submitButton').prop('disabled', true).text('<?php echo get_phrase('submitting'); ?>...');

        $.ajax({
            url: '<?php echo site_url('admin/quiz_questions/'.$param2.'/add'); ?>',
            type: 'post',
            data: $('form#mcq_form').serialize(),
            success: function(response) {
                $('#submitButton').prop('disabled', false).text('<?php echo get_phrase('submit_quiz_question'); ?>');
                if (response == 1) {
                    success_notify('<?php echo get_phrase('question_has_been_added'); ?>');
                    showLargeModal('<?php echo site_url('modal/popup/quiz_questions/'.$param2); ?>', '<?php echo get_phrase('manage_quiz_questions'); ?>');
                } else {
                    error_notify(response);
                }
            },
            error: function() {
                $('#submitButton').prop('disabled', false).text('<?php echo get_phrase('submit_quiz_question'); ?>');
                error_notify('<?php echo get_phrase('an_error_occurred'); ?>');
            }
        });
    }

    $(function(){
        initSummerNote(['#question_title']);
        $('.select2').select2();
    });
</script>
