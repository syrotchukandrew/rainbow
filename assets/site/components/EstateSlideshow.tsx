import React, { useEffect, useState } from 'react';
import { Button } from '@/components/ui/button';

interface Props {
    images: string[];
    title: string;
}

export default function EstateSlideshow({ images, title }: Props) {
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
        <div className="relative mb-5">
            <img
                src={images[current]}
                alt={title}
                className="w-full max-h-96 object-cover"
            />
            {images.length > 1 && (
                <div className="text-center mt-2">
                    <Button
                        variant="secondary"
                        size="sm"
                        onClick={() => setCurrent(i => (i - 1 + images.length) % images.length)}
                    >
                        ‹
                    </Button>
                    <span className="mx-2">{current + 1} / {images.length}</span>
                    <Button
                        variant="secondary"
                        size="sm"
                        onClick={() => setCurrent(i => (i + 1) % images.length)}
                    >
                        ›
                    </Button>
                </div>
            )}
        </div>
    );
}
