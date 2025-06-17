<?php

class block_coursefeedback extends block_base
{
    public function init()
    {
        $this->title = get_string('coursefeedback', 'block_coursefeedback');
    }

    public function get_content()
    {
        global $USER, $COURSE, $OUTPUT, $DB;

        if ($this->content !== null) {
            return $this->content;
        }

        $context = context_course::instance($COURSE->id);
        $this->content = new stdClass();

        if (has_capability('block/coursefeedback:viewdashboard', $context)) {
            // Show average ratings
            $sql = "SELECT 
                    AVG(contentquality) AS avg_content,
                    AVG(instructoreffectiveness) AS avg_instructor,
                    AVG(coursematerials) AS avg_materials,
                    AVG(workloaddifficulty) AS avg_workload
                FROM {block_coursefeedback}
                WHERE courseid = :courseid";

            $averages = $DB->get_record_sql($sql, ['courseid' => $COURSE->id]);

            if ($averages && $averages->avg_content !== null) {
                $this->content->text = "<strong>📊 Average Ratings:</strong><br>" .
                    "📚 Content Quality: " . round($averages->avg_content, 1) . "<br>" .
                    "🧑‍🏫 Instructor Effectiveness: " . round($averages->avg_instructor, 1) . "<br>" .
                    "📄 Course Materials: " . round($averages->avg_materials, 1) . "<br>" .
                    "🔧 Workload Difficulty: " . round($averages->avg_workload, 1) . "<br><br>";

                // Link to dashboard
                $url = new moodle_url('/blocks/coursefeedback/teacher_dashboard.php', ['courseid' => $COURSE->id]);
                $this->content->text .= html_writer::link($url, get_string('viewdashboard', 'block_coursefeedback'));
            } else {
                $this->content->text = "No feedback yet.";
            }

        } elseif (has_capability('block/coursefeedback:givefeedback', $context)) {
            $url = new moodle_url('/blocks/coursefeedback/feedback.php', ['courseid' => $COURSE->id]);
            $this->content->text = html_writer::link($url, get_string('givefeedback', 'block_coursefeedback'));

        } else {
            $this->content->text = get_string('nocapability', 'block_coursefeedback');
        }

        return $this->content;
    }

}
