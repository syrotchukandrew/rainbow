import React, { useState } from 'react';

interface Props {
    action: string;
    csrf: string;
    error: string;
    lastUsername: string;
}

export default function LoginForm({ action, csrf, error, lastUsername }: Props) {
    const [username, setUsername] = useState(lastUsername);
    const [password, setPassword] = useState('');

    return (
        <div className="row">
            <div className="col-sm-5">
                <div className="well">
                    {error && (
                        <div className="alert alert-danger">{error}</div>
                    )}
                    <form method="POST" action={action}>
                        <div className="form-group">
                            <label htmlFor="login_username">Логін</label>
                            <input
                                id="login_username"
                                type="text"
                                name="_username"
                                autoComplete="username"
                                value={username}
                                onChange={e => setUsername(e.target.value)}
                                className="form-control"
                                autoFocus
                            />
                        </div>
                        <div className="form-group">
                            <label htmlFor="login_password">Пароль</label>
                            <input
                                id="login_password"
                                type="password"
                                name="_password"
                                autoComplete="current-password"
                                value={password}
                                onChange={e => setPassword(e.target.value)}
                                className="form-control"
                            />
                        </div>
                        <input type="hidden" name="_csrf_token" value={csrf} />
                        <button type="submit" className="btn btn-primary">Увійти</button>
                    </form>
                </div>
            </div>
        </div>
    );
}
