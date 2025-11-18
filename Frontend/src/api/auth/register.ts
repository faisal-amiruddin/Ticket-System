import api from "../../lib/axios"

interface UserRegister {
    name: string,
    email: string,
    password: string
}

export async function registerUser(payload: UserRegister) {
    const response = await api.post("/register", payload);
    return response.data;
}