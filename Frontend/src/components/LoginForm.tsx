import { useQueryClient } from "@tanstack/react-query";

export const LoginForm = () =>  {
    const queryClient = useQueryClient();

    return (
        <div className="container">
            <h1>Hello World</h1>
        </div>
    );
}