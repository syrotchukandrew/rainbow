import React, { useState } from 'react';

interface Props {
    action: string;
    csrf: string;
    errors: string[];
}

export default function RegisterForm({ action, csrf, errors }: Props) {
    const [username, setUsername]     = useState('');
    const [email, setEmail]           = useState('');
    const [password, setPassword]     = useState('');
    const [passwordRepeat, setRepeat] = useState('');

    return (
        <div className="row">
            <div className="col-sm-5">
                <div className="well">
                    {errors.length > 0 && (
                        <div className="alert alert-danger">
                            <ul className="mb-0">
                                {errors.map((e) => <li key={e}>{e}</li>)}
                            </ul>
                        </div>
                    )}
                    <form method="POST" action={action}>
                        <div className="form-group">
                            <label htmlFor="reg_username">Логін</label>
                            <input
                                id="reg_username"
                                type="text"
                                name="app_bundle_user_type[username]"
                                autoComplete="username"
                                value={username}
                                onChange={e => setUsername(e.target.value)}
                                className="form-control"
                            />
                        </div>
                        <div className="form-group">
                            <label htmlFor="reg_email">Email</label>
                            <input
                                id="reg_email"
                                type="email"
                                name="app_bundle_user_type[email]"
                                autoComplete="email"
                                value={email}
                                onChange={e => setEmail(e.target.value)}
                                className="form-control"
                            />
                        </div>
                        <div className="form-group">
                            <label htmlFor="reg_password">Пароль</label>
                            <input
                                id="reg_password"
                                type="password"
                                name="app_bundle_user_type[plainPassword][first]"
                                autoComplete="new-password"
                                value={password}
                                onChange={e => setPassword(e.target.value)}
                                className="form-control"
                            />
                        </div>
                        <div className="form-group">
                            <label htmlFor="reg_password_repeat">Повторіть пароль</label>
                            <input
                                id="reg_password_repeat"
                                type="password"
                                name="app_bundle_user_type[plainPassword][second]"
                                autoComplete="new-password"
                                value={passwordRepeat}
                                onChange={e => setRepeat(e.target.value)}
                                className="form-control"
                            />
                        </div>
                        <input type="hidden" name="app_bundle_user_type[_token]" value={csrf} />
                        <button type="submit" className="btn btn-primary">Зареєструватись!</button>
                    </form>
                </div>
            </div>
        </div>
    );
}
