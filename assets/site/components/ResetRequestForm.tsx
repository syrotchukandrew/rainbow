import React, { useState } from 'react';

interface Props {
    action: string;
    csrf: string;
    loginUrl: string;
}

export default function ResetRequestForm({ action, csrf, loginUrl }: Props) {
    const [email, setEmail] = useState('');

    return (
        <div className="max-w-sm mx-auto py-8">
            <div className="bg-white border border-gray-200 rounded-lg shadow-sm p-6">
                <h1 className="text-xl font-semibold text-gray-900 mb-6">Відновлення паролю</h1>
                <form method="POST" action={action}>
                    <div className="mb-6">
                        <label htmlFor="reset_email" className="block text-sm font-medium text-gray-700 mb-1">
                            Email
                        </label>
                        <input
                            id="reset_email"
                            type="email"
                            name="password_reset_request_type[email]"
                            autoComplete="email"
                            value={email}
                            onChange={e => setEmail(e.target.value)}
                            className="block w-full px-3 py-2 border border-gray-300 rounded-md text-sm bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-400"
                        />
                    </div>
                    <input type="hidden" name="password_reset_request_type[_token]" value={csrf} />
                    <button
                        type="submit"
                        className="w-full px-4 py-2 bg-primary text-primary-foreground rounded-md text-sm font-medium hover:opacity-90"
                    >
                        Надіслати
                    </button>
                </form>
                <p className="mt-4 text-sm text-center">
                    <a href={loginUrl} className="text-gray-600 hover:text-gray-900">Увійти</a>
                </p>
            </div>
        </div>
    );
}
