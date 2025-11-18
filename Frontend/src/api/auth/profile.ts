import api from "../../lib/axios";

export async function profile() {
    const response = await api.get("/me");
    return response.data;
}