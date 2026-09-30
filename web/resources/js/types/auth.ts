export type UserType = "owner" | "staff" | "customer";

export type User = {
    id: number;
    full_name: string;
    email: string;
    user_type: UserType;
    organization_id: number | null;
    is_active: boolean;
    is_manager: boolean;
    avatar?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
    [key: string]: unknown; // This allows for additional properties...
};

export type Auth = {
    user: User | null;
};
