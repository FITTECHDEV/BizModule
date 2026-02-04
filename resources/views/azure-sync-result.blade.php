<!DOCTYPE html>
<html>
<head>
    <title>Azure Apps Sync Result</title>
    <style>
        body { font-family: Arial; padding: 20px; }
        .success { color: green; }
        .error { color: red; }
        .info { color: blue; }
    </style>
</head>
<body>
    <h2>Azure Apps Sync - Result</h2>
    
    <p class="info"><strong>User:</strong> {{ $user->displayName }} ({{ $user->userPrincipalName }})</p>
    <p class="info"><strong>Azure ID:</strong> {{ $user->AzureId }}</p>
    
    <h3>Modules Found in Database:</h3>
    <ul>
        @foreach($modules as $module)
            <li>{{ $module }}</li>
        @endforeach
    </ul>
    
    <h3>Azure Assignment Results:</h3>
    @foreach($results as $module => $result)
        <div style="margin: 10px 0;">
            <strong>{{ $module }}:</strong>
            @if($result['success'])
                <span class="success">✓ {{ $result['status'] }}</span>
            @else
                <span class="error">✗ {{ $result['error'] ?? 'Failed' }}</span>
            @endif
        </div>
    @endforeach
    
    <hr>
    <p><a href="{{ route('azure.users.show', $user) }}">← Back to user profile</a></p>
</body>
</html>
```

## Visit:
```
https://your-domain.com/azure-sync-user-apps/kinoti.m@freight-in-time.com