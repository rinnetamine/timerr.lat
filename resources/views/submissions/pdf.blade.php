<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Submission #{{ $submission->id }} - Timerr</title>
    <style>
        body { font-family: Arial, Helvetica, sans-serif; color: #0f172a; }
        .container { max-width: 800px; margin: 24px auto; padding: 20px; }
        h1 { font-size: 20px; margin-bottom: 8px; }
        .muted { color: #55617a; font-size: 13px; }
        .box { border: 1px solid #e6eef5; padding: 12px; border-radius: 6px; margin-bottom: 12px; }
        .label { font-weight: bold; font-size: 13px; color: #0a2540; }
        .value { margin-top: 6px; color: #0f172a; }
        .small { font-size: 12px; color: #475569; }
        .files { margin-top: 8px; }
        .file { margin-bottom: 6px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Submission #{{ $submission->id }} — {{ $submission->jobListing->title }}</h1>
        <div class="muted">Generated: {{ now()->toDateTimeString() }}</div>

        <div class="box">
            <div class="label">Status</div>
            <div class="value">{{ ucfirst($submission->status) }}</div>
        </div>

        <div class="box">
            <div class="label">Applicant</div>
            <div class="value">{{ $submission->user->first_name }} {{ $submission->user->last_name }} &lt;{{ $submission->user->email }}&gt;</div>
            <div class="small">User ID: {{ $submission->user->id }}</div>
        </div>

        <div class="box">
            <div class="label">Help request</div>
            <div class="value">{{ $submission->jobListing->title }}</div>
            <div class="small">Category: {{ $submission->jobListing->category }} — Credits: {{ $submission->jobListing->time_credits }}</div>
        </div>

        <div class="box">
            <div class="label">Message</div>
            <div class="value">{!! nl2br(e($submission->message)) !!}</div>
        </div>

        @if($submission->admin_notes)
            <div class="box">
                <div class="label">Admin notes</div>
                <div class="value">{!! nl2br(e($submission->admin_notes)) !!}</div>
            </div>
        @endif

        @if($submission->files->count())
            <div class="box">
                <div class="label">Attached files</div>
                <div class="files">
                    @foreach($submission->files as $file)
                        <div class="file">• {{ $file->file_name }} ({{ round($file->file_size/1024, 1) }} KB)</div>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="small">This document was exported from Timerr. For more details visit the site.</div>
    </div>
</body>
</html>
