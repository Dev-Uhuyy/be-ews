<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error Logs Dashboard</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f5f7fa;
            min-height: 100vh;
            color: #1a202c;
            line-height: 1.5;
        }

        .container {
            max-width: 1440px;
            margin: 0 auto;
            padding: 24px;
        }

        .header {
            background: white;
            border-radius: 12px;
            padding: 32px;
            margin-bottom: 24px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            border: 1px solid #e5e7eb;
        }

        .header h1 {
            font-size: 1.875rem;
            font-weight: 600;
            color: #1a202c;
            margin-bottom: 8px;
        }

        .header p {
            color: #6b7280;
            font-size: 1rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }

        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 24px;
            text-align: left;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            border: 1px solid #e5e7eb;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .stat-number {
            font-size: 1.75rem;
            font-weight: 600;
            color: #1063b9;
            margin-bottom: 4px;
        }

        .stat-label {
            color: #6b7280;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .table-container {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            border: 1px solid #e5e7eb;
        }

        .table-header {
            background: #1063b9;
            color: white;
            padding: 20px 24px;
        }

        .table-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .table-subtitle {
            font-size: 0.875rem;
            opacity: 0.9;
        }

        .table-wrapper {
            overflow-x: auto;
            max-height: 70vh;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }

        th {
            background: #f9fafb;
            color: #1a202c;
            font-weight: 500;
            padding: 12px 16px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        td {
            padding: 12px 16px;
            border-bottom: 1px solid #e5e7eb;
            vertical-align: middle;
        }

        tr:hover {
            background: #f9fafb;
        }

        .id-cell {
            font-weight: 500;
            color: #1063b9;
        }

        .method-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 500;
            text-transform: uppercase;
        }

        .method-get { background: #e6fffa; color: #047857; }
        .method-post { background: #fef3e2; color: #d97706; }
        .method-put { background: #e0f2fe; color: #0369a1; }
        .method-delete { background: #fee2e2; color: #dc2626; }

        .url-cell {
            font-family: 'Fira Code', monospace;
            font-size: 0.875rem;
            color: #4b5563;
            max-width: 200px;
            word-break: break-all;
        }

        .message-cell {
            max-width: 300px;
            word-wrap: break-word;
            font-size: 0.875rem;
        }

        .code-block {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 8px;
            font-family: 'Fira Code', monospace;
            font-size: 0.75rem;
            max-width: 250px;
            max-height: 150px;
            overflow: auto;
            white-space: pre-wrap;
            word-wrap: break-word;
        }

        .time-cell {
            color: #6b7280;
            font-size: 0.875rem;
            white-space: nowrap;
        }

        .pagination {
            padding: 24px;
            text-align: center;
        }

        .pagination a, .pagination span {
            display: inline-block;
            padding: 8px 14px;
            margin: 0 4px;
            border-radius: 6px;
            text-decoration: none;
            color: #1063b9;
            background: white;
            border: 1px solid #e5e7eb;
            transition: all 0.2s ease;
            font-size: 0.875rem;
        }

        .pagination a:hover {
            background: #1063b9;
            color: white;
            border-color: #1063b9;
        }

        .pagination .current {
            background: #1063b9;
            color: white;
            border-color: #1063b9;
        }

        /* Modal Styles */
        .modal {
            display: flex;
            justify-content: center;
            align-items: center;
            position: fixed;
            z-index: 9999;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
        }

        .modal-content {
            background: white;
            padding: 32px;
            border-radius: 12px;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            transform: translateY(0);
        }

        .modal h2 {
            color: #1a202c;
            margin-bottom: 16px;
            font-size: 1.5rem;
            font-weight: 600;
        }

        .error-message {
            background: #fef2f2;
            color: #dc2626;
            padding: 8px 12px;
            border-radius: 6px;
            margin-bottom: 16px;
            border-left: 3px solid #dc2626;
            font-size: 0.875rem;
        }

        .form-group {
            margin-bottom: 16px;
        }

        input[type="password"] {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            font-size: 0.875rem;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
            background: white;
        }

        input[type="password"]:focus {
            outline: none;
            border-color: #1063b9;
            box-shadow: 0 0 0 3px rgba(16, 99, 185, 0.1);
        }

        .submit-btn {
            background: #1063b9;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 6px;
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            width: 100%;
        }

        .submit-btn:hover {
            background: #0e5599;
            box-shadow: 0 2px 8px rgba(16, 99, 185, 0.2);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .container {
                padding: 16px;
            }

            .header h1 {
                font-size: 1.25rem;
            }

            .stats-grid {
                grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
                gap: 12px;
            }

            .table-wrapper {
                font-size: 0.875rem;
            }

            th, td {
                padding: 10px 12px;
            }

            .modal-content {
                margin: 16px;
                padding: 24px;
            }
        }

        @media (max-width: 480px) {
            .header {
                padding: 16px;
            }

            .header h1 {
                font-size: 1.25rem;
            }

            .stat-card {
                padding: 16px;
            }

            .table-header {
                padding: 12px 16px;
            }

            th, td {
                padding: 8px 10px;
            }
        }
    </style>
</head>
<body>
    @php use Illuminate\Support\Str; @endphp

    @if (!$authorized)
        <div class="modal">
            <div class="modal-content">
                <h2>🔐 Access Required</h2>
                @if (isset($error))
                    <div class="error-message">{{ $error }}</div>
                @endif
                <form method="POST" action="{{ route('logs.index') }}">
                    @csrf
                    <div class="form-group">
                        <input type="password" name="password" placeholder="Enter your password..." required />
                    </div>
                    <button type="submit" class="submit-btn">Access Dashboard</button>
                </form>
            </div>
        </div>
    @else
        <div class="container">
            <div class="header">
                <h1>🚨 Error Logs Dashboard STI API</h1>
                <p>Monitor and analyze application errors in real-time</p>
            </div>


            <div class="table-container">
                <div class="table-header">
                    <div class="table-title">Error Log Entries</div>
                    <div class="table-subtitle">Detailed view of application errors and exceptions</div>
                </div>

                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>User</th>
                                <th>Method</th>
                                <th>URL</th>
                                <th>Error Message</th>
                                <th>Payload</th>
                                <th>Stack Trace</th>
                                <th>Timestamp</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($logs as $log)
                                <tr>
                                    <td class="id-cell">#{{ $log->id }}</td>
                                    <td>{{ $log->user_id ?? 'Guest' }}</td>
                                    <td>
                                        <span class="method-badge method-{{ strtolower($log->method) }}">
                                            {{ $log->method }}
                                        </span>
                                    </td>
                                    <td class="url-cell">{{ $log->url }}</td>
                                    <td class="message-cell">{{ $log->message }}</td>
                                    <td>
                                        <div class="code-block">{{ json_encode(json_decode($log->payload), JSON_PRETTY_PRINT) }}</div>
                                    </td>
                                    <td>
                                        <div class="code-block">{{ Str::limit($log->trace, 500) }}</div>
                                    </td>
                                    <td class="time-cell">{{ $log->created_at->format('M d, Y H:i:s') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="pagination"></div>


            </div>
        </div>
    @endif
</body>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const rowsPerPage = 10; // jumlah row per halaman
        const table = document.querySelector('table tbody');
        const rows = Array.from(table.querySelectorAll('tr'));
        const paginationContainer = document.querySelector('.pagination');

        let currentPage = 1;
        const totalPages = Math.ceil(rows.length / rowsPerPage);

        function displayRows(page) {
            const start = (page - 1) * rowsPerPage;
            const end = start + rowsPerPage;
            rows.forEach((row, index) => {
                row.style.display = (index >= start && index < end) ? '' : 'none';
            });
        }

        function createPagination() {
            paginationContainer.innerHTML = '';

            // Prev button
            const prevBtn = document.createElement('a');
            prevBtn.href = '#';
            prevBtn.textContent = 'Prev';
            prevBtn.classList.toggle('disabled', currentPage === 1);
            prevBtn.addEventListener('click', (e) => {
                e.preventDefault();
                if (currentPage > 1) {
                    currentPage--;
                    updatePagination();
                }
            });
            paginationContainer.appendChild(prevBtn);

            // Number buttons
            for(let i = 1; i <= totalPages; i++) {
                const pageBtn = document.createElement('a');
                pageBtn.href = '#';
                pageBtn.textContent = i;
                if (i === currentPage) pageBtn.classList.add('current');
                pageBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    currentPage = i;
                    updatePagination();
                });
                paginationContainer.appendChild(pageBtn);
            }

            // Next button
            const nextBtn = document.createElement('a');
            nextBtn.href = '#';
            nextBtn.textContent = 'Next';
            nextBtn.classList.toggle('disabled', currentPage === totalPages);
            nextBtn.addEventListener('click', (e) => {
                e.preventDefault();
                if (currentPage < totalPages) {
                    currentPage++;
                    updatePagination();
                }
            });
            paginationContainer.appendChild(nextBtn);
        }

        function updatePagination() {
            displayRows(currentPage);
            createPagination();
        }

        // Initialize
        updatePagination();
    });
    </script>

</html>