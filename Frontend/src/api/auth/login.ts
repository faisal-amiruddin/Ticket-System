import api from "../../lib/axios"

interface UserLogin {
    email: string,
    password: string
}

export async function loginUser(payload: UserLogin) {
    const response = await api.post("/login", payload);
    return response.data;
}