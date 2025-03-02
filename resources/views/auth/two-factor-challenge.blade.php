<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Two Factor Authentication</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">Two Factor Authentication</div>

                    <div class="card-body">
                        <p class="text-center">
                            Please enter your authentication code to login.
                        </p>

                        <form method="POST" action="{{ route('two-factor.login') }}">
                            @csrf

                            <div class="mb-3">
                                <label for="code" class="form-label">Authentication Code</label>
                                <input id="code" type="text" class="form-control @error('code') is-invalid @enderror" name="code" required autofocus>
                                @error('code')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="recovery_code" id="recovery_code" value="1">
                                    <label class="form-check-label" for="recovery_code">
                                        Use a recovery code
                                    </label>
                                </div>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">
                                    Verify
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 