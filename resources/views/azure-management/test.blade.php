<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Azure AD Integration Test</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 2rem;
            min-height: 100vh;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .header {
            text-align: center;
            color: white;
            margin-bottom: 2rem;
        }
        
        .header h1 {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
        }
        
        .header p {
            font-size: 1.1rem;
            opacity: 0.9;
        }
        
        .test-card {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .test-card h2 {
            font-size: 1.5rem;
            margin-bottom: 1rem;
            color: #2d3748;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .status-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status-success {
            background: #10b981;
            color: white;
        }
        
        .status-failed {
            background: #ef4444;
            color: white;
        }
        
        .status-warning {
            background: #f59e0b;
            color: white;
        }
        
        .test-details {
            margin-top: 1rem;
        }
        
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 0.75rem;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .detail-row:last-child {
            border-bottom: none;
        }
        
        .detail-label {
            font-weight: 600;
            color: #4b5563;
        }
        
        .detail-value {
            color: #1f2937;
            text-align: right;
            word-break: break-all;
            max-width: 60%;
        }
        
        .error-box {
            background: #fee2e2;
            border-left: 4px solid #ef4444;
            padding: 1rem;
            border-radius: 4px;
            margin-top: 1rem;
        }
        
        .error-box pre {
            color: #991b1b;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        
        .success-box {
            background: #d1fae5;
            border-left: 4px solid #10b981;
            padding: 1rem;
            border-radius: 4px;
            margin-top: 1rem;
        }
        
        .success-box p {
            color: #065f46;
        }
        
        .warning-box {
            background: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 1rem;
            border-radius: 4px;
            margin-top: 1rem;
        }
        
        .warning-box p {
            color: #92400e;
        }
        
        .refresh-btn {
            background: #667eea;
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            display: block;
            margin: 2rem auto 0;
            transition: all 0.3s;
        }
        
        .refresh-btn:hover {
            background: #5a67d8;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .icon {
            width: 24px;
            height: 24px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔍 Azure AD Integration Test</h1>
            <p>Testing Azure Graph API connectivity and user provisioning</p>
        </div>

        <!-- TEST 1: Configuration -->
        <div class="test-card">
            <h2>
                <span class="icon">⚙️</span>
                Test 1: Configuration Check
                <span class="status-badge {{ $results['config']['status'] === 'success' ? 'status-success' : 'status-failed' }}">
                    {{ $results['config']['status'] }}
                </span>
            </h2>
            
            @if($results['config']['status'] === 'success')
                <div class="test-details">
                    <div class="detail-row">
                        <span class="detail-label">Tenant ID:</span>
                        <span class="detail-value">{{ $results['config']['tenant_id'] }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Client ID:</span>
                        <span class="detail-value">{{ $results['config']['client_id'] }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Client Secret:</span>
                        <span class="detail-value">{{ $results['config']['client_secret'] }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Default Domain:</span>
                        <span class="detail-value">{{ $results['config']['domain'] }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Graph API URL:</span>
                        <span class="detail-value">{{ $results['config']['graph_url'] }}</span>
                    </div>
                </div>
            @else
                <div class="error-box">
                    <pre>{{ $results['config']['error'] }}</pre>
                </div>
            @endif
        </div>

        <!-- TEST 2: Access Token -->
        <div class="test-card">
            <h2>
                <span class="icon">🔑</span>
                Test 2: Azure AD Authentication
                <span class="status-badge {{ $results['token']['status'] === 'success' ? 'status-success' : 'status-failed' }}">
                    {{ $results['token']['status'] }}
                </span>
            </h2>
            
            @if($results['token']['status'] === 'success')
                <div class="success-box">
                    <p><strong>{{ $results['token']['message'] }}</strong></p>
                </div>
                <div class="test-details">
                    <div class="detail-row">
                        <span class="detail-label">Token Preview:</span>
                        <span class="detail-value" style="font-family: monospace; font-size: 0.875rem;">{{ $results['token']['token_preview'] }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Token Length:</span>
                        <span class="detail-value">{{ $results['token']['token_length'] }} characters</span>
                    </div>
                </div>
            @else
                <div class="error-box">
                    <p><strong>{{ $results['token']['message'] }}</strong></p>
                    <pre>{{ $results['token']['error'] }}</pre>
                </div>
            @endif
        </div>

        <!-- TEST 3: Find User -->
        @if(isset($results['find_user']['status']))
        <div class="test-card">
            <h2>
                <span class="icon">🔎</span>
                Test 3: User Lookup
                <span class="status-badge {{ $results['find_user']['status'] === 'success' ? 'status-success' : 'status-failed' }}">
                    {{ $results['find_user']['status'] }}
                </span>
            </h2>
            
            @if($results['find_user']['status'] === 'success')
                @if($results['find_user']['found'])
                    <div class="warning-box">
                        <p><strong>{{ $results['find_user']['message'] }}</strong></p>
                    </div>
                    <div class="test-details">
                        <div class="detail-row">
                            <span class="detail-label">Azure ID:</span>
                            <span class="detail-value">{{ $results['find_user']['azure_id'] }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">UPN:</span>
                            <span class="detail-value">{{ $results['find_user']['upn'] }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Display Name:</span>
                            <span class="detail-value">{{ $results['find_user']['display_name'] }}</span>
                        </div>
                    </div>
                @else
                    <div class="success-box">
                        <p><strong>{{ $results['find_user']['message'] }}</strong></p>
                    </div>
                @endif
            @else
                <div class="error-box">
                    <pre>{{ $results['find_user']['error'] }}</pre>
                </div>
            @endif
        </div>
        @endif

        <!-- TEST 4: Create User -->
        @if(isset($results['create_user']['status']))
        <div class="test-card">
            <h2>
                <span class="icon">👤</span>
                Test 4: User Creation
                <span class="status-badge {{ $results['create_user']['status'] === 'success' ? 'status-success' : 'status-failed' }}">
                    {{ $results['create_user']['status'] }}
                </span>
            </h2>
            
            @if($results['create_user']['status'] === 'success')
                <div class="success-box">
                    <p><strong>{{ $results['create_user']['message'] }}</strong></p>
                </div>
                <div class="test-details">
                    <div class="detail-row">
                        <span class="detail-label">Azure ID:</span>
                        <span class="detail-value">{{ $results['create_user']['azure_id'] }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">UPN:</span>
                        <span class="detail-value">{{ $results['create_user']['azure_upn'] }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Display Name:</span>
                        <span class="detail-value">{{ $results['create_user']['display_name'] }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Password Generated:</span>
                        <span class="detail-value">{{ $results['create_user']['password_generated'] ? '✅ Yes' : '❌ No' }}</span>
                    </div>
                </div>
                @if(isset($results['create_user']['note']))
                <div class="warning-box" style="margin-top: 1rem;">
                    <p><strong>⚠️ Note:</strong> {{ $results['create_user']['note'] }}</p>
                </div>
                @endif
            @else
                <div class="error-box">
                    <p><strong>{{ $results['create_user']['message'] }}</strong></p>
                    <pre>{{ $results['create_user']['error'] }}</pre>
                </div>
            @endif
        </div>
        @endif

        <button class="refresh-btn" onclick="window.location.reload()">
            🔄 Run Tests Again
        </button>
    </div>
</body>
</html>