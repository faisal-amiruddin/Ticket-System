import { QueryClient, QueryClientProvider } from "@tanstack/react-query"
import { LoginForm } from "./components/LoginForm";

const queryClient = new QueryClient();

function App() {

  return (
    <QueryClientProvider client={queryClient}>
      <LoginForm />
    </QueryClientProvider>
  )
}

export default App
