import React from "react";

interface LogoProps extends React.HTMLAttributes<HTMLDivElement> {
    className?: string;
    classNameIcon?: string;
}

export default function Logo({
    className = "",
    classNameIcon = "",
    ...props
}: LogoProps) {
    return (
        <div
            className={`grid h-10 w-10 place-items-center rounded-icon bg-action-dark text-action-dark-foreground ${className}`}
            {...props}
        >
            <span className="flex items-end gap-0.5">
                <span
                    className={`h-2 w-1 rounded-pill ${classNameIcon}`}
                />
                <span
                    className={`h-3 w-1 rounded-pill ${classNameIcon}`}
                />
                <span
                    className={`h-2 w-1 rounded-pill ${classNameIcon}`}
                />
            </span>
        </div>
    );
}
