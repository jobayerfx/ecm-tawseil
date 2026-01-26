<?php

namespace Modules\Aitools\Http\Controllers;

use App\Helper\Reply;
use App\Http\Controllers\AccountBaseController;
use Modules\Aitools\Entities\AiToolsSetting;
use Modules\Aitools\Entities\AiToolsUsageHistory;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Request;

class AiRephraseController extends AccountBaseController
{
    /**
     * Rephrase text using AI
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function rephraseText(Request $request)
    {
        abort_403(!(module_enabled('Aitools') && (in_array('admin', user_roles()) || user()->permission('view_aitools') == 'all')));
        $text = $request->input('text');

        if (empty($text)) {
            return Reply::error(__('aitools::messages.pleaseEnterSomeText'));
        }

        try {
            // Check if AI tools are configured
            $aiSetting = AiToolsSetting::where('company_id', company()->id)->first();
            
            if (!$aiSetting || empty($aiSetting->chatgpt_api_key)) {
                return Reply::error(__('aitools::messages.aiToolsNotConfigured'));
            }

            $client = new Client();
            
            // Get the selected model or use default
            $model = $aiSetting->model_name ?: 'gpt-4o-mini';
            

            $response = $client->request('POST', 'https://api.openai.com/v1/responses', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $aiSetting->chatgpt_api_key,
                    'Content-Type'  => 'application/json',
                ],
                'json' => [
                    'model' => $model,
                    'input' => "no suggestion just rewrite this text =   \n\n" . $text,
                ],
                'timeout' => 30,
            ]);
            
            $data = json_decode($response->getBody(), true);
            
            // Safely extract the text output
            $rephrasedText = $data['output'][1]['content'][0]['text'] ?? '';
            $rephrasedText = (!$rephrasedText) ? $data['output'][0]['content'][0]['text'] ?? '' : $rephrasedText;

            if (!$rephrasedText) {
                return Reply::error(__('aitools::messages.unexpectedResponse'));
            }

            // Track usage - try multiple possible response formats
            $usage = $data['usage'] ?? $data['data']['usage'] ?? [];
            // Try different possible locations for usage data
            $promptTokens = $usage['prompt_tokens'] ?? $usage['input_tokens'] ?? $usage['n_context_tokens_total'] ?? 0;
            $completionTokens = $usage['completion_tokens'] ?? $usage['output_tokens'] ?? $usage['n_generated_tokens_total'] ?? 0;
            $totalTokens = $usage['total_tokens'] ?? ($promptTokens + $completionTokens);
            
            // If still no tokens, estimate based on prompt and response length
            if ($totalTokens == 0) {
                // Rough estimation: ~4 characters per token for English text
                $promptTokens = max(1, (int) ceil(mb_strlen($text) / 4));
                $completionTokens = max(1, (int) ceil(mb_strlen($rephrasedText) / 4));
                $totalTokens = $promptTokens + $completionTokens;
            }

            // Track usage - update single record instead of creating new ones
            try {
                $companyId = company()->id;
                $usageRecord = AiToolsUsageHistory::where('company_id', $companyId)->first();
                
                if ($usageRecord) {
                    // Update existing record by adding to current values
                    $usageRecord->update([
                        'model' => $model,
                        'prompt' => $text,
                        'response' => mb_substr($rephrasedText, 0, 1000),
                        'prompt_tokens' => ($usageRecord->prompt_tokens ?? 0) + $promptTokens,
                        'completion_tokens' => ($usageRecord->completion_tokens ?? 0) + $completionTokens,
                        'total_tokens' => ($usageRecord->total_tokens ?? 0) + $totalTokens, 
                        'total_requests' => ($usageRecord->total_requests ?? 0) + 1,
                    ]);
                } else {
                    // Create first record
                    AiToolsUsageHistory::create([
                        'company_id' => $companyId,
                        'user_id' => user()->id,
                        'model' => $model,
                        'prompt' => $text,
                        'response' => mb_substr($rephrasedText, 0, 1000),
                        'prompt_tokens' => $promptTokens,
                        'completion_tokens' => $completionTokens,
                        'total_tokens' => $totalTokens,
                        'total_requests' => 1,
                    ]);
                }
            } catch (\Exception $e) {
                // Log error but don't fail the request
                logger()->error('Failed to save usage history', ['error' => $e->getMessage()]);
            }

            return Reply::dataOnly([
                'status' => 'success',
                'rephrased_text' => trim($rephrasedText)
            ]);

        } catch (RequestException $e) {
            $errorMessage = __('aitools::messages.aiRequestFailed');
            
            if ($e->hasResponse()) {
                try {
                    $errorResponse = json_decode($e->getResponse()->getBody(), true);
                    if (isset($errorResponse['error']['message'])) {
                        $errorMessage = $errorResponse['error']['message'];
                    }
                } catch (\Exception $parseException) {
                    // Use default message
                }
            }
            
            return Reply::error($errorMessage);
        } catch (GuzzleException $e) {
            return Reply::error(__('aitools::messages.networkError'));
        } catch (\Exception $e) {
            logger()->error('Rephrase text error', ['error' => $e->getMessage()]);
            return Reply::error(__('aitools::messages.somethingWentWrong'));
        }
    }
}

