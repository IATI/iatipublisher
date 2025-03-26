import axios, { AxiosError } from 'axios';

const apiClient = axios.create({
  baseURL: '/',
});

class LanguageService {
  static handleError(error: AxiosError | unknown) {
    console.error('API call failed:', error);
    throw error;
  }

  static async getLanguage() {
    try {
      const response = await apiClient.get('/current-language');
      return response.data.data;
    } catch (error: unknown) {
      this.handleError(error as AxiosError);
    }
  }

  static async changeLanguage(language: string) {
    try {
      const response = await apiClient.get(`/language/${language}`);
      return response.data;
    } catch (error) {
      this.handleError(error);
    }
  }

  static async getTranslatedData(queries: string) {
    try {
      const response = await apiClient.get(`/translated-data`, {
        params: { folders: queries },
      });
      return response.data;
    } catch (error) {
      this.handleError(error);
    }
  }
}

export default LanguageService;
