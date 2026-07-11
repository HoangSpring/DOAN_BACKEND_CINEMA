import { createBrowserRouter, RouterProvider } from 'react-router-dom';
import CustomerLayout from './layouts/CustomerLayout';
import HomePage from './pages/HomePage';
import MovieDetailsPage from './pages/MovieDetailsPage';

const router = createBrowserRouter([
  {
    path: '/',
    element: <CustomerLayout />,
    children: [
      {
        index: true,
        element: <HomePage />,
      },
      {
        path: 'movies/:id',
        element: <MovieDetailsPage />,
      },
      // Placeholder for other routes required in subsequent prompts
      {
        path: 'showtimes/:id/seats',
        element: <div>Trang Chọn Ghế (Chưa implement)</div>,
      },
      {
        path: 'login',
        element: <div>Trang Đăng Nhập (Chưa implement)</div>,
      },
    ],
  },
  {
    path: '/admin',
    element: <div>Admin Layout (Chưa implement)</div>,
  },
  {
    path: '/staff',
    element: <div>Staff Layout (Chưa implement)</div>,
  },
]);

function App() {
  return <RouterProvider router={router} />;
}

export default App;
