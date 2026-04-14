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
        <div className="max-w-sm mx-auto py-8">
            <div className="bg-white border border-gray-200 rounded-lg shadow-sm p-6">
                <h1 className="text-xl font-semibold text-gray-900 mb-6">Увійти</h1>
                {error && (
                    <div className="mb-4 p-3 bg-red-50 border border-red-200 rounded text-sm text-red-700">
                        {error}
                    </div>
                )}
                <form method="POST" action={action}>
                    <div className="mb-4">
                        <label htmlFor="login_username" className="block text-sm font-medium text-gray-700 mb-1">
                            Логін
                        </label>
                        <input
                            id="login_username"
                            type="text"
                            name="_username"
                            autoComplete="username"
                            value={username}
                            onChange={e => setUsername(e.target.value)}
                            className="block w-full px-3 py-2 border border-gray-300 rounded-md text-sm bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-400"
                            autoFocus
                        />
                    </div>
                    <div className="mb-6">
                        <label htmlFor="login_password" className="block text-sm font-medium text-gray-700 mb-1">
                            Пароль
                        </label>
                        <input
                            id="login_password"
                            type="password"
                            name="_password"
                            autoComplete="current-password"
                            value={password}
                            onChange={e => setPassword(e.target.value)}
                            className="block w-full px-3 py-2 border border-gray-300 rounded-md text-sm bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-400"
                        />
                    </div>
                    <input type="hidden" name="_csrf_token" value={csrf} />
                    <button
                        type="submit"
                        className="w-full px-4 py-2 bg-primary text-primary-foreground rounded-md text-sm font-medium hover:opacity-90"
                    >
                        Увійти
                    </button>
                </form>
            </div>
        </div>
    );
}
