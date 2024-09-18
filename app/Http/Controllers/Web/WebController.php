<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\IATI\Traits\IatiTranslationTrait;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;

/**
 * Class WebController.
 */
class WebController extends Controller
{
    /**
     * Shows the web page.
     *
     * @param string $page
     * @return Renderable
     */
    public function index(string $page = 'signin'): Renderable
    {
        try {
            [$message, $intent] = $this->updateMessageIntent();

            return view('web.welcome', compact('page', 'intent', 'message'));
        } catch (\Exception $e) {
            logger()->error($e->getMessage());
        }
    }

    /**
     * Check and update message for user redirection to login page.
     *
     * @return array
     */
    private function updateMessageIntent(): array
    {
        $message = '';
        $intent = '';

        if (Str::contains(Redirect::intended()->getTargetUrl(), '/email/verify/')) {
            $message = trans('validation.logged_in_verify');
            $intent = 'verify';
        }

        return [$message, $intent];
    }

    /**
     * Shows the web page.
     *
     * @return Renderable
     */
    public function register(): Renderable
    {
        return view('web.register');
    }

    /**
     * Shows the about page.
     *
     * @return Renderable
     */
    public function about(): Renderable
    {
        return view('web.about');
    }

    /**
     * Shows the publisher checklist page.
     *
     * @return Renderable
     */
    public function publishingChecklist(): Renderable
    {
        return view('web.publishing_checklist');
    }

    /**
     * Shows the iati standard checklist page.
     *
     * @return Renderable
     */
    public function iatiStandard(): Renderable
    {
        return view('web.iati_standard');
    }

    /**
     * Shows the support checklist page.
     *
     * @return Renderable
     */
    public function support(): Renderable
    {
        return view('web.support');
    }

    /**
     * Updates the locale of the system.
     *
     * @param $language
     *
     * @return JsonResponse
     */
    public function setLocale($language): JsonResponse
    {
        try {
            if (!in_array($language, ['en', 'fr', 'es'])) {
                return response()->json(['success' => false, 'message' => 'Invalid language code.']);
            }

            session()->put('locale', 'en');

            return response()->json(['success' => true, 'message' => 'Locale changed successfully.']);
        } catch (\Exception $e) {
            logger()->error($e);

            return response()->json(['success' => false, 'message' => 'Error has occurred when changing locale.']);
        }
    }

    /**
     * Returns the translated data for the given folder.
     *
     * @param  Request  $request
     *
     * @return JsonResponse
     */
    public function getTranslatedData(Request $request): JsonResponse
    {
        try {
            $folders = $request->get('folders');

            if (!$folders) {
                return response()->json(['success' => false, 'message' => 'No folders provided.']);
            }
            $translator = new class {
                use IatiTranslationTrait;
            };
            $folders = explode(',', $folders);
            $cacheData = $translator->loadTranslations();
            $requiredTranslations = $translator->filterTranslations($cacheData, $folders);

            return response()->json(['success' => true, 'data' => Arr::dot($requiredTranslations)]);
        } catch (\Exception $e) {
            logger()->error($e);

            return response()->json(['success' => false, 'message' => 'Error has occurred when returning translated data.']);
        }
    }

    /**
     * Returns the locale of the system.
     *
     * @return JsonResponse
     */
    public function getLocale(): JsonResponse
    {
        try {
            return response()->json(['success' => true, 'data' => App::getLocale(), 'message' => 'Locale retrieved successfully.']);
        } catch (\Exception $e) {
            logger()->error($e);

            return response()->json(['success' => false, 'message' => 'Error has occurred when changing locale.']);
        }
    }
}
