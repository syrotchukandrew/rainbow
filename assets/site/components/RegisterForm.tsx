import React, { useState } from 'react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';

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
        <div className="max-w-sm mx-auto py-8">
            <div className="bg-white border border-gray-200 rounded-lg shadow-sm p-6">
                <h1 className="text-xl font-semibold text-gray-900 mb-6">Реєстрація</h1>
                {errors.length > 0 && (
                    <div className="mb-4 p-3 bg-red-50 border border-red-200 rounded text-sm text-red-700">
                        <ul className="list-disc list-inside space-y-1">
                            {errors.map((e) => <li key={e}>{e}</li>)}
                        </ul>
                    </div>
                )}
                <form method="POST" action={action}>
                    <div className="mb-4">
                        <label htmlFor="reg_username" className="block text-sm font-medium text-gray-700 mb-1">
                            Логін
                        </label>
                        <Input
                            id="reg_username"
                            type="text"
                            name="app_bundle_user_type[username]"
                            autoComplete="username"
                            value={username}
                            onChange={e => setUsername(e.target.value)}
                        />
                    </div>
                    <div className="mb-4">
                        <label htmlFor="reg_email" className="block text-sm font-medium text-gray-700 mb-1">
                            Email
                        </label>
                        <Input
                            id="reg_email"
                            type="email"
                            name="app_bundle_user_type[email]"
                            autoComplete="email"
                            value={email}
                            onChange={e => setEmail(e.target.value)}
                        />
                    </div>
                    <div className="mb-4">
                        <label htmlFor="reg_password" className="block text-sm font-medium text-gray-700 mb-1">
                            Пароль
                        </label>
                        <Input
                            id="reg_password"
                            type="password"
                            name="app_bundle_user_type[plainPassword][first]"
                            autoComplete="new-password"
                            value={password}
                            onChange={e => setPassword(e.target.value)}
                        />
                    </div>
                    <div className="mb-6">
                        <label htmlFor="reg_password_repeat" className="block text-sm font-medium text-gray-700 mb-1">
                            Повторіть пароль
                        </label>
                        <Input
                            id="reg_password_repeat"
                            type="password"
                            name="app_bundle_user_type[plainPassword][second]"
                            autoComplete="new-password"
                            value={passwordRepeat}
                            onChange={e => setRepeat(e.target.value)}
                        />
                    </div>
                    <input type="hidden" name="app_bundle_user_type[_token]" value={csrf} />
                    <Button type="submit" className="w-full">
                        Зареєструватись
                    </Button>
                </form>
            </div>
        </div>
    );
}
