import React, { useState } from 'react';
import { Button } from '@/components/ui/button';

interface Props {
    slug: string;
    csrf: string;
    placeholder: string;
    submit: string;
}

export default function CommentForm({ slug, csrf, placeholder, submit }: Props) {
    const [content, setContent] = useState('');
    const [status, setStatus] = useState<'idle' | 'sending' | 'success' | 'error'>('idle');
    const [errorMsg, setErrorMsg] = useState('');

    async function handleSubmit(e: React.FormEvent) {
        e.preventDefault();
        if (!content.trim()) return;

        setStatus('sending');
        try {
            const response = await fetch(`/api/estate/${slug}/comment`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': csrf,
                },
                body: JSON.stringify({ content }),
            });

            if (response.status === 201) {
                setStatus('success');
                setContent('');
            } else {
                const data = await response.json();
                setErrorMsg(data.error ?? 'Error');
                setStatus('error');
            }
        } catch {
            setStatus('error');
            setErrorMsg('Network error');
        }
    }

    return (
        <form onSubmit={handleSubmit}>
            <div className="mb-3">
                <label htmlFor="comment_content" className="sr-only">{placeholder}</label>
                <textarea
                    id="comment_content"
                    className="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-gray-400 resize-none disabled:opacity-50"
                    rows={4}
                    placeholder={placeholder}
                    value={content}
                    onChange={e => setContent(e.target.value)}
                    disabled={status === 'sending'}
                />
            </div>
            {status === 'success' && (
                <div className="mb-3 rounded bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">Comment submitted for review.</div>
            )}
            {status === 'error' && (
                <div className="mb-3 rounded bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">{errorMsg}</div>
            )}
            <Button
                type="submit"
                disabled={status === 'sending' || !content.trim()}
            >
                {status === 'sending' ? '...' : submit}
            </Button>
        </form>
    );
}
