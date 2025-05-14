<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\File;

class ReportService
{
    protected array $studentData;
    protected array $studentResponses;
    protected array $questions;

    public function __construct()
    {
        $this->studentData = json_decode(File::get(base_path('reports/students.json')), true);
        $this->studentResponses = json_decode(File::get(base_path('reports/student-responses.json')), true);
        $this->questions = json_decode(File::get(base_path('reports/questions.json')), true);
    }

    // generate diagnostic report
    public function generateDiagnosticReport($student_id)
    {
        if (!$student_id || !$report_id) {
            throw new \Exception('Student or report not found');
        }

        $strands = ['Number and Algebra', 'Measurement and Geometry', 'Statistics and Probability'];

        // Prepare correct answers grouped by strand
        $correctAnswersByStrand = collect($this->questions)
            ->groupBy('strand')
            ->map(function ($questions) {
                $ids = $questions->pluck('id')->all();
                $keys = $questions->pluck('config.key')->all();
                return array_combine($ids, $keys);
            });

        foreach ($this->studentResponses as $studentResponse) {
            if ($studentResponse['student']['id'] != $student_id) {
                continue;
            }

            $student = collect($this->studentData)
                ->where('id', $studentResponse['student']['id'])
                ->first();

            $name = trim($student['firstName'] . ' ' . $student['lastName']);

            $completedAt = isset($studentResponse['completed'])
                ? Carbon::createFromFormat('d/m/Y H:i:s', $studentResponse['completed'])->format('jS F Y g:i A')
                : 'Unknown Time';

            $responses = collect($studentResponse['responses']);

            $totalQuestions = 0;
            $totalCorrect = 0;
            $strandBreakdown = [];

            foreach ($strands as $strand) {
                $correctAnswers = $correctAnswersByStrand[$strand] ?? [];

                $strandResponses = $responses->whereIn('questionId', array_keys($correctAnswers));

                $correctCount = $strandResponses->filter(function ($resp) use ($correctAnswers) {
                    return isset($correctAnswers[$resp['questionId']]) &&
                        $correctAnswers[$resp['questionId']] === $resp['response'];
                })->count();

                $total = count($correctAnswers);
                $totalQuestions += $total;
                $totalCorrect += $correctCount;

                $strandBreakdown[] = "$strand: {$correctCount} out of {$total} correct";
            }
 
            echo "{$name} recently completed Numeracy assessment on {$completedAt}\n";
            echo "He got {$totalCorrect} questions right out of {$totalQuestions}. Details by strand given below:\n\n";

            foreach ($strandBreakdown as $line) {
                echo $line . "\n";
            }

            echo "\n-------------------------------------\n\n";

            // or return the data as an array
            // return [
            //     'student_id' => $student_id,
            //     'report_id' => $report_id,
            //     'name' => $name,
            //     'completed_at' => $completedAt,
            //     'total_correct' => $totalCorrect,
            //     'total_questions' => $totalQuestions,
            //     'strand_breakdown' => $strandBreakdown
            // ];
        }
    }


    // generate progress report
    public function generateProgressReport($student_id)
    {
        $student = collect($this->studentData)->firstWhere('id', $student_id);
        if (!$student) {
            throw new \Exception("Student not found");
        }

        $name = trim($student['firstName'] . ' ' . $student['lastName']);

        // Get all responses for the student
        $studentAttempts = collect($this->studentResponses)
        ->filter(fn($r) => $r['student']['id'] == $student_id && isset($r['completed']))
        ->sortBy(fn($r) => Carbon::createFromFormat('d/m/Y H:i:s', $r['completed']));

        if ($studentAttempts->isEmpty()) {
            echo "{$name} has not completed any valid assessments.\n";
            return;
        }

        $attemptSummaries = $studentAttempts->map(function ($r) {
            $date = Carbon::createFromFormat('d/m/Y H:i:s', $r['completed'])->format('jS F Y');
            $score = $r['results']['rawScore'] ?? 0;
            return [
                'date' => $date,
                'score' => $score
            ];
        })->values();

        // Get total questions (assumed to be from the full question set)
        $totalQuestions = count($this->questions);
        

        echo "{$name} has completed Numeracy assessment {$attemptSummaries->count()} times in total. Date and raw score given below:\n\n";

        foreach ($attemptSummaries as $attempt) {
            echo "Date: {$attempt['date']}, Raw Score: {$attempt['score']} out of {$totalQuestions}\n";
        }

        // Show improvement from oldest to most recent
        $oldest = $attemptSummaries->first();
        $latest = $attemptSummaries->last();
        $diff = $latest['score'] - $oldest['score'];

        if ($diff > 0) {
            echo "\n{$name} got {$diff} more correct in the recent completed assessment than the oldest.\n";
        } elseif ($diff < 0) {
            echo "\n{$name} got " . abs($diff) . " fewer correct in the recent completed assessment than the oldest.\n";
        } else {
            echo "\n{$name} got the same score in the oldest and most recent assessments.\n";
        }

        // or return the data as an array
            // return [
            //     'student_id' => $student_id,
            //     'report_id' => $report_id,
            //     'name' => $name,
            //     'completed_at' => $completedAt,
            //     'total_correct' => $totalCorrect,
            //     'total_questions' => $totalQuestions,
            //     'strand_breakdown' => $strandBreakdown
            // ];
    }

    // generate feedback report
    public function generateFeedbackReport($student_id)
    {
       
    }
}