<?php

namespace Point\Core\Http\Controllers;

use Point\Core\Helpers\QueueHelper;

class BugReportController extends Controller
{
    /**
     * Send bugs report
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function _send()
    {
        $filePath = app('request')->input('project')->url . '/bug-report/';
        $fileLink = "";

        if (strlen($_FILES['file']['tmp_name'])) {
            $name = $_FILES['file']['name'];
            $upload = \Storage::put($filePath . $name, file_get_contents($_FILES['file']['tmp_name']));

            if (!$upload) {
                $response = array('status' => 'failed', 'message' => 'cannot upload picture');
                return response()->json($response);
            }
            $fileLink = storage_path() . '/app/' . $filePath . '/' . $name;
        }

        $report = array(
            'title' => $_POST['title'],
            'message' => $_POST['message'],
            'plugins' => $_POST['plugins'],
            'file' => $fileLink
        );

        $data = [
            'report' => $report,
            'username' => auth()->user()->name,
            'email' => auth()->user()->email,
            'project_url' => app('request')->input('project')->url,
            'database_name' => \DB::connection()->getDatabaseName()
        ];

        \Queue::push(function ($job) use ($data) {
            QueueHelper::reconnectAppDatabase($data['database_name']);
            \Mail::send('core::emails.bug-report', $data, function ($message) use ($data) {
                $message->to(env('MAIL_BUG_REPORT'))->cc(env('MAIL_BUG_REPORT_2'))->subject($data['project_url'] . ' [' . $data['report']['plugins'] . '] : ' . $data['report']['title']);
                if ($data['report']['file'] !== "") {
                    $message->attach($data['report']['file']);
                }
            });
            $job->delete();
        });

        $response = array('status' => 'success', 'message' => $report);
        return response()->json($response);
    }
}
