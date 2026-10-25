export type User = {
    id: number;
    name: string;
    email: string;
    role?: string;
    avatar?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
    operateur?: {
        id: number;
        nom: string;
        user_id?: number;
    };
    [key: string]: unknown;
};

export type Auth = {
    user: User;
};
