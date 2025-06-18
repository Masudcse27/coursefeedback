<?php

class block_coursefeedback extends block_base
{
    public function init()
    {
        $this->title = get_string('coursefeedback', 'block_coursefeedback');
    }

    private function render_star_rating($rating, $maxstars = 5, $size = 24)
    {
        global $OUTPUT;
        $rating = max(0, min($rating, $maxstars));
        $percentage = ($rating / $maxstars) * 100;

        return $OUTPUT->render_from_template('block_coursefeedback/star_rating', [
            'percentage' => $percentage,
            'size' => $size
        ]);
    }

    public function get_content()
    {
        global $COURSE, $DB, $USER;

        if ($this->content !== null) {
            return $this->content;
        }

        $context = context_course::instance($COURSE->id);
        $this->content = new stdClass();

        if (has_capability('block/coursefeedback:viewdashboard', $context)) {

            $sql = "SELECT 
                        AVG(contentquality) AS avg_content,
                        AVG(instructoreffectiveness) AS avg_instructor,
                        AVG(coursematerials) AS avg_materials,
                        AVG(workloaddifficulty) AS avg_workload
                    FROM {block_coursefeedback}
                    WHERE courseid = :courseid";

            $averages = $DB->get_record_sql($sql, ['courseid' => $COURSE->id]);

            if ($averages && $averages->avg_content !== null) {
                $overall = (
                    (float) $averages->avg_content +
                    (float) $averages->avg_instructor +
                    (float) $averages->avg_materials +
                    (float) $averages->avg_workload
                ) / 4;

                $stars = $this->render_star_rating($overall);

                $this->content->text = "" . $stars . "<br>" .
                    "<strong>Average Ratings: " . number_format($overall, 2) . "</strong><br>" .
                    "Content Quality: " . $this->render_star_rating($averages->avg_content) . "<br>" .
                    "Instructor Effectiveness: " . $this->render_star_rating($averages->avg_instructor) . "<br>" .
                    "Course Materials: " . $this->render_star_rating($averages->avg_materials) . "<br>" .
                    "Workload Difficulty: " . $this->render_star_rating($averages->avg_workload) . "<br><br>";

                $url = new moodle_url('/blocks/coursefeedback/teacher_dashboard.php', ['courseid' => $COURSE->id]);
                $this->content->text .= html_writer::link($url, get_string('viewdashboard', 'block_coursefeedback')) . "<br><br>";
            } else {
                $this->content->text = "No feedback yet.<br><br>";
            }
        } elseif (has_capability('block/coursefeedback:givefeedback', $context)) {
            $userfeedback = $DB->get_record('block_coursefeedback', [
                'userid' => $USER->id,
                'courseid' => $COURSE->id
            ]);

            if ($userfeedback) {
                $this->content->text .= "<strong>🧑‍🎓 Your Feedback:</strong><br>" .
                    "Content Quality: " . $this->render_star_rating($userfeedback->contentquality) . "<br>" .
                    "Instructor Effectiveness: " . $this->render_star_rating($userfeedback->instructoreffectiveness) . "<br>" .
                    "Course Materials: " . $this->render_star_rating($userfeedback->coursematerials) . "<br>" .
                    "Workload Difficulty: " . $this->render_star_rating($userfeedback->workloaddifficulty) . "<br><br>";
            }

            $url = new moodle_url('/blocks/coursefeedback/feedback.php', ['courseid' => $COURSE->id]);
            $this->content->text .= html_writer::link($url, $userfeedback ? get_string('change_feedback', 'block_coursefeedback') : get_string('givefeedback', 'block_coursefeedback'));
        } else {
            $this->content->text = get_string('nocapability', 'block_coursefeedback');
        }

        return $this->content;
    }
}
