import React from 'react';
import { Button } from '@/components/ui/button';

export default function HelloIsland(): React.JSX.Element {
    const [clicked, setClicked] = React.useState(false);

    return (
        <div className="p-4 border border-green-500 rounded-md bg-green-50 inline-block">
            <p className="text-green-800 font-medium mb-2">
                ✓ React island loaded
            </p>
            <Button
                variant="outline"
                size="sm"
                onClick={() => setClicked(!clicked)}
            >
                {clicked ? 'It works!' : 'Click to test'}
            </Button>
        </div>
    );
}
