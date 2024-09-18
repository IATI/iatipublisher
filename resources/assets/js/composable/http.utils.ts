import axios from 'axios';

const http = () => {
  const instance = axios.create();

  /*
   * Intercepting response
   * */
  instance.interceptors.response.use(
    (response) => response,
    (error) => {
      if (error.response.status === 401) {
        // redirect to login page
      }

      return Promise.reject(error);
    }
  );

  return instance;
};

export default http;
