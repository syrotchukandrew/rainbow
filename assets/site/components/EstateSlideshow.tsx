import React, { useEffect, useState } from 'react';

interface Props {
    images: string[];
}

export default function EstateSlideshow({ images }: Props) {
    const [current, setCurrent] = useState(0);

    useEffect(() => {
        if (images.length <= 1) return;
        const timer = setInterval(() => {
            setCurrent(i => (i + 1) % images.length);
        }, 4000);
        return () => clearInterval(timer);
    }, [images.length]);

    if (images.length === 0) return null;

    return (
        <div style={{ position: 'relative', marginBottom: '20px' }}>
            <img
                src={images[current]}
                alt=""
                style={{ width: '100%', maxHeight: '400px', objectFit: 'cover' }}
            />
            {images.length > 1 && (
                <div style={{ textAlign: 'center', marginTop: '8px' }}>
                    <button
                        className="btn btn-default btn-sm"
                        onClick={() => setCurrent(i => (i - 1 + images.length) % images.length)}
                    >
                        ‹
                    </button>
                    <span style={{ margin: '0 8px' }}>{current + 1} / {images.length}</span>
                    <button
                        className="btn btn-default btn-sm"
                        onClick={() => setCurrent(i => (i + 1) % images.length)}
                    >
                        ›
                    </button>
                </div>
            )}
        </div>
    );
}
