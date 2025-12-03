<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scheduled Maintenance - IATI Publisher</title>
    <style>
        :root {
            --primary: #155366;
            --secondary: #28a084;
            --text: #333333;
            --text-light: #666666;
            --background: #f5f7fa;
            --card-bg: #ffffff;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
            background-color: var(--background);
            color: var(--text);
            line-height: 1.6;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        .maintenance-container {
            max-width: 600px;
            width: 100%;
            background-color: var(--card-bg);
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            padding: 40px;
            text-align: center;
        }

        .logo {
            margin-bottom: 30px;
        }

        .logo svg {
            height: 50px;
            width: auto;
            fill: var(--primary);
        }

        h1 {
            font-size: 24px;
            margin-bottom: 24px;
            color: var(--primary);
            font-weight: 600;
        }

        .time-banner {
            background-color: #e6f0fa;
            border-left: 4px solid var(--primary);
            border-radius: 4px;
            padding: 16px;
            margin-bottom: 30px;
            text-align: left;
        }

        .time-banner strong {
            color: var(--secondary);
            font-size: 18px;
            display: block;
            margin-bottom: 5px;
        }

        .maintenance-message {
            margin-bottom: 30px;
            color: var(--text);
            font-size: 16px;
            line-height: 1.7;
            text-align: left;
        }

        .contact-info {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            color: var(--text-light);
            font-size: 14px;
        }

        .contact-info a {
            color: var(--primary);
            text-decoration: none;
        }

        .contact-info a:hover {
            text-decoration: underline;
        }

        .gear-icon {
            margin: 20px 0;
            animation: spin 10s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
<div class="maintenance-container">
    <div class="logo">
        <!-- IATI-style logo placeholder -->
        <img src="/images/header-logo.svg" alt="">
    </div>

    <h1>@lang('custom_pages/maintenance_mode.sorry_iati_publisher_is_under_maintenance')</h1>

    <div class="time-banner">
        {{-- DYNAMIC TIMERANGE HERE --}}
        <strong>
            @lang('custom_pages/maintenance_mode.scheduled_maintenance', [
                'timerange' => Cache::get('maintenance_timerange', 'Scheduled Maintenance')
            ])
        </strong>
    </div>

    <div class="maintenance-message">
        @if(Cache::get('maintenance_type') === 'maintenance')
            <p>@lang('custom_pages/maintenance_mode.iati_publisher_is_temporarily_unavailable', ['time'=> Cache::get('maintenance_timerange', 'soon')])</p>
        @else
            <p>@lang('custom_pages/maintenance_mode.IATI_Publisher_is_unavailable_this_week_while_we_replace_the_IATI_Registry')</p>
            <p><a href="https://www.iaticonnect.org/technical-cop/topic/planned-downtime-1-5th-dec-iati-registry-replacement-underway">@lang('custom_pages/maintenance_mode.read_more_about_this_work_on_IATI_Connect')</a></p>
        @endif
        <br>
        <p>@lang('custom_pages/maintenance_mode.we_appreciate_your_understanding')</p>
    </div>

    <div class="contact-info">
        <p>@lang('custom_pages/maintenance_mode.if_you_have_any_questions_or_issues', ['email' => '<strong><a href="mailto:support@iatistandard.org">support@iatistandard.org</a></strong>'])</p>
    </div>

</div>
</body>
</html>
