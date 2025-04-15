import axios, { AxiosError } from 'axios';

const apiClient = axios.create({
  baseURL: '/',
});

class ImportService {
  static handleError(error: AxiosError | unknown) {
    console.error('API call failed:', error);
    throw error;
  }

  static async cancelOngoingImports() {
    try {
      const response = await apiClient.delete('/import/delete-ongoing-import');
      return response.data;
    } catch (error: unknown) {
      this.handleError(error as AxiosError);
    }
  }

  static async cancelXLS() {
    try {
      const response = await apiClient.delete('/import/xls');
      return response.data;
    } catch (error: unknown) {
      this.handleError(error as AxiosError);
    }
  }
}

export default ImportService;
