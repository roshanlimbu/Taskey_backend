<?php

namespace App\Http\Controllers\Sadmin;

use App\Http\Controllers\Controller;
use App\Models\Reports;
use App\Models\Task;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use OpenAI\Laravel\Facades\OpenAI;

class OpenAiController extends Controller
{
    public function prompt(Request  $request)
    {
        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'title' => 'nullable|string|max:255',
        ]);
        $projectId = $request->input('project_id');
        $project = Project::find($projectId);
        $projectName = $project->name ?? '';
        $projectDescription = $project->description ?? '';
        $tasks = Task::where('project_id', $projectId)->get(['title', 'description', 'status']);
        $maxTasks = 10;
        $taskList = "";
        foreach ($tasks->take($maxTasks) as $i => $task) {
            $taskList .= "\n" . ($i+1) . ". **{$task->title}**\n   Description: {$task->description}\n   Status: {$task->status}";
        }
        if ($tasks->count() > $maxTasks) {
            $taskList .= "\n...and more tasks not shown.";
        }
        $prompt = "Generate a report for the project \"$projectName\" (ID: $projectId).\nDescription: $projectDescription\nHere are the tasks and their descriptions: $taskList";
        Log::info("OpenAI Prompt: " . $prompt);

        try {
            $response = OpenAI::chat()->create([
                'model' => 'gpt-4',
                'messages' => [
                    ['role' => 'user', 'content' => $prompt],
                ],
                'max_tokens' => 150,
            ]);

            // save
            $report = Reports::create([
                'project_id' => $request->input('project_id'),
                'user_id' => Auth::id(),
                'title' => $request->input('title', 'Generated Report'),
                'report' => $response->choices[0]->message->content,
            ]);
            Log::info("Generated report: " . $report->report);

            return response()->json([
                'success' => true,
                'report_id' => $report->id,
                'response' => $report->report,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
