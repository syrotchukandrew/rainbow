import React, { useState } from 'react';

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
            <div className="form-group">
                <textarea
                    className="form-control"
                    rows={4}
                    placeholder={placeholder}
                    value={content}
                    onChange={e => setContent(e.target.value)}
                    disabled={status === 'sending'}
                />
            </div>
            {status === 'success' && (
                <div className="alert alert-success">Comment submitted for review.</div>
            )}
            {status === 'error' && (
                <div className="alert alert-danger">{errorMsg}</div>
            )}
            <button
                type="submit"
                className="btn btn-primary"
                disabled={status === 'sending' || !content.trim()}
            >
                {status === 'sending' ? '...' : submit}
            </button>
        </form>
    );
}
