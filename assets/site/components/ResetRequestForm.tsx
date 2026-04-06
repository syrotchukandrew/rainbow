import React, { useState } from 'react';

interface Props {
    action: string;
    csrf: string;
    loginUrl: string;
}

export default function ResetRequestForm({ action, csrf, loginUrl }: Props) {
    const [email, setEmail] = useState('');

    return (
        <div className="row">
            <div className="col-sm-5">
                <div className="well">
                    <h2>Відновлення паролю</h2>
                    <form method="POST" action={action}>
                        <div className="form-group">
                            <label htmlFor="reset_email">Email</label>
                            <input
                                id="reset_email"
                                type="email"
                                name="password_reset_request_type[email]"
                                value={email}
                                onChange={e => setEmail(e.target.value)}
                                className="form-control"
                            />
                        </div>
                        <input type="hidden" name="password_reset_request_type[_token]" value={csrf} />
                        <button type="submit" className="btn btn-primary">Надіслати</button>
                    </form>
                    <a href={loginUrl}>Увійти</a>
                </div>
            </div>
        </div>
    );
}
