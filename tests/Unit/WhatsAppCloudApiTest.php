<?php

namespace Netflie\WhatsAppCloudApi\Tests\Unit;

use Netflie\WhatsAppCloudApi\Client;
use Netflie\WhatsAppCloudApi\Http\ClientHandler;
use Netflie\WhatsAppCloudApi\Http\RawResponse;
use Netflie\WhatsAppCloudApi\Message\Document\DocumentId;
use Netflie\WhatsAppCloudApi\Message\Document\DocumentLink;
use Netflie\WhatsAppCloudApi\Message\Media\LinkID;
use Netflie\WhatsAppCloudApi\Message\Media\MediaObjectID;
use Netflie\WhatsAppCloudApi\Message\Template\Component;
use Netflie\WhatsAppCloudApi\Tests\WhatsAppCloudApiTestConfiguration;
use Netflie\WhatsAppCloudApi\WhatsAppCloudApi;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;

/**
 * @group unit
 */
final class WhatsAppCloudApiTest extends TestCase
{
    use ProphecyTrait;

    private $whatsapp_app_cloud_api;
    private $client_handler;
    private $faker;

    public function setUp(): void
    {
        $this->faker = \Faker\Factory::create();
        $this->client_handler = $this->prophesize(ClientHandler::class);

        $this->whatsapp_app_cloud_api = new WhatsAppCloudApi([
            'from_phone_number_id' => WhatsAppCloudApiTestConfiguration::$from_phone_number_id,
            'access_token' => WhatsAppCloudApiTestConfiguration::$access_token,
            'client_handler' => $this->client_handler->reveal(),
        ]);
    }

    public function test_send_text_message()
    {
        $to = $this->faker->phoneNumber;
        $url = $this->buildRequestUri();
        $text_message = $this->faker->text;
        $preview_url = $this->faker->boolean;

        $body = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $to,
            'type' => 'text',
            'text' => [
                'preview_url' => $preview_url,
                'body' => $text_message,
            ],
        ];
        $encoded_body = json_encode($body);
        $headers = [
            'Authorization' => 'Bearer ' . WhatsAppCloudApiTestConfiguration::$access_token,
            'Content-Type' => 'application/json',
        ];

        $this->client_handler
            ->send($url, $encoded_body, $headers, Argument::type('int'))
            ->shouldBeCalled()
            ->willReturn(new RawResponse($headers, $encoded_body, 200));

        $response = $this->whatsapp_app_cloud_api->sendTextMessage(
            $to,
            $text_message,
            $preview_url
        );

        $this->assertEquals(200, $response->httpStatusCode());
        $this->assertEquals($body, $response->decodedBody());
        $this->assertEquals($encoded_body, $response->body());
        $this->assertEquals(false, $response->isError());
    }

    public function test_send_document_id()
    {
        $to = $this->faker->phoneNumber;
        $url = $this->buildRequestUri();
        $caption = $this->faker->text;
        $filename = $this->faker->text;
        $document_id = $this->faker->uuid;

        $body = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $to,
            'type' => 'document',
            'document' => [
                'caption' => $caption,
                'filename' => $filename,
                'id' => $document_id,
            ],
        ];
        $encoded_body = json_encode($body);
        $headers = [
            'Authorization' => 'Bearer ' . WhatsAppCloudApiTestConfiguration::$access_token,
            'Content-Type' => 'application/json',
        ];

        $this->client_handler
            ->send($url, $encoded_body, $headers, Argument::type('int'))
            ->shouldBeCalled()
            ->willReturn(new RawResponse($headers, $encoded_body, 200));

        $media_id = new MediaObjectID($document_id);
        $response = $this->whatsapp_app_cloud_api->sendDocument(
            $to,
            $media_id,
            $filename, $caption
        );

        $this->assertEquals(200, $response->httpStatusCode());
        $this->assertEquals($body, $response->decodedBody());
        $this->assertEquals($encoded_body, $response->body());
        $this->assertEquals(false, $response->isError());
    }

    public function test_send_document_link()
    {
        $to = $this->faker->phoneNumber;
        $url = $this->buildRequestUri();
        $caption = $this->faker->text;
        $filename = $this->faker->text;
        $document_link = $this->faker->url;

        $body = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $to,
            'type' => 'document',
            'document' => [
                'caption' => $caption,
                'filename' => $filename,
                'link' => $document_link,
            ],
        ];
        $encoded_body = json_encode($body);
        $headers = [
            'Authorization' => 'Bearer ' . WhatsAppCloudApiTestConfiguration::$access_token,
            'Content-Type' => 'application/json',
        ];

        $this->client_handler
            ->send($url, $encoded_body, $headers, Argument::type('int'))
            ->shouldBeCalled()
            ->willReturn(new RawResponse($headers, $encoded_body, 200));

        $link_id = new LinkID($document_link);
        $response = $this->whatsapp_app_cloud_api->sendDocument(
            $to,
            $link_id,
            $filename, $caption
        );

        $this->assertEquals(200, $response->httpStatusCode());
        $this->assertEquals($body, $response->decodedBody());
        $this->assertEquals($encoded_body, $response->body());
        $this->assertEquals(false, $response->isError());
    }

    public function test_send_template_without_components()
    {
        $to = $this->faker->phoneNumber;
        $url = $this->buildRequestUri();
        $template_name = $this->faker->name;
        $language = $this->faker->locale;

        $body = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $to,
            'type' => 'template',
            'template' => [
                'name' => $template_name,
                'language' => ['code' => $language],
                'components' => [],
            ],
        ];
        $encoded_body = json_encode($body);
        $headers = [
            'Authorization' => 'Bearer ' . WhatsAppCloudApiTestConfiguration::$access_token,
            'Content-Type' => 'application/json',
        ];

        $this->client_handler
            ->send($url, $encoded_body, $headers, Argument::type('int'))
            ->shouldBeCalled()
            ->willReturn(new RawResponse($headers, $encoded_body, 200));

        $response = $this->whatsapp_app_cloud_api->sendTemplate(
            $to,
            $template_name,
            $language
        );

        $this->assertEquals(200, $response->httpStatusCode());
        $this->assertEquals($body, $response->decodedBody());
        $this->assertEquals($encoded_body, $response->body());
        $this->assertEquals(false, $response->isError());
    }


    public function test_send_template_with_components()
    {
        $to = $this->faker->phoneNumber;
        $url = $this->buildRequestUri();
        $template_name = $this->faker->name;
        $language = $this->faker->locale;

        $component_header = [
            [
                'type' => 'text',
                'text' => 'I\'m a heder',
            ],
        ];
        $component_body = [
            [
                'type' => 'text',
                'text' => '*Mr Jones*',
            ],
        ];
        $component_buttons = [
            [
                'type' => 'button',
                'sub_type' => 'quick_reply',
                'index' => 0,
                'parameters' => [
                    [
                        'type' => 'text',
                        'text' => 'Yes',
                    ]
                ]
            ],
            [
                'type' => 'button',
                'sub_type' => 'quick_reply',
                'index' => 1,
                'parameters' => [
                    [
                        'type' => 'text',
                        'text' => 'No',
                    ]
                ]
            ]
        ];

        $components = new Component($component_header, $component_body, $component_buttons);

        $body = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $to,
            'type' => 'template',
            'template' => [
                'name' => $template_name,
                'language' => ['code' => $language],
                'components' => [
                    [
                        'type' => 'header',
                        'parameters' => $component_header,
                    ],
                    [
                        'type' => 'body',
                        'parameters' => $component_body,
                    ],
                    [
                        'type' => 'button',
                        'sub_type' => 'quick_reply',
                        'index' => 0,
                        'parameters' => [
                            [
                                'type' => 'text',
                                'text' => 'Yes',
                            ]
                        ]
                    ],
                    [
                        'type' => 'button',
                        'sub_type' => 'quick_reply',
                        'index' => 1,
                        'parameters' => [
                            [
                                'type' => 'text',
                                'text' => 'No',
                            ]
                        ]
                    ],
                ],
            ],
        ];
        $encoded_body = json_encode($body);
        $headers = [
            'Authorization' => 'Bearer ' . WhatsAppCloudApiTestConfiguration::$access_token,
            'Content-Type' => 'application/json',
        ];

        $this->client_handler
            ->send($url, $encoded_body, $headers, Argument::type('int'))
            ->shouldBeCalled()
            ->willReturn(new RawResponse($headers, $encoded_body, 200));

        $components = new Component($component_header, $component_body, $component_buttons);
        $response = $this->whatsapp_app_cloud_api->sendTemplate(
            $to,
            $template_name,
            $language,
            $components
        );

        $this->assertEquals(200, $response->httpStatusCode());
        $this->assertEquals($body, $response->decodedBody());
        $this->assertEquals($encoded_body, $response->body());
        $this->assertEquals(false, $response->isError());
    }

    public function test_send_audio_id()
    {
        $to = $this->faker->phoneNumber;
        $url = $this->buildRequestUri();
        $document_id = $this->faker->uuid;

        $body = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $to,
            'type' => 'audio',
            'audio' => [
                'id' => $document_id,
            ],
        ];
        $encoded_body = json_encode($body);
        $headers = [
            'Authorization' => 'Bearer ' . WhatsAppCloudApiTestConfiguration::$access_token,
            'Content-Type' => 'application/json',
        ];

        $this->client_handler
            ->send($url, $encoded_body, $headers, Argument::type('int'))
            ->shouldBeCalled()
            ->willReturn(new RawResponse($headers, $encoded_body, 200));

        $media_id = new MediaObjectID($document_id);
        $response = $this->whatsapp_app_cloud_api->sendAudio(
            $to,
            $media_id
        );

        $this->assertEquals(200, $response->httpStatusCode());
        $this->assertEquals($body, $response->decodedBody());
        $this->assertEquals($encoded_body, $response->body());
        $this->assertEquals(false, $response->isError());
    }

    public function test_send_audio_link()
    {
        $to = $this->faker->phoneNumber;
        $url = $this->buildRequestUri();
        $document_link = $this->faker->url;

        $body = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $to,
            'type' => 'audio',
            'audio' => [
                'link' => $document_link,
            ],
        ];
        $encoded_body = json_encode($body);
        $headers = [
            'Authorization' => 'Bearer ' . WhatsAppCloudApiTestConfiguration::$access_token,
            'Content-Type' => 'application/json',
        ];

        $this->client_handler
            ->send($url, $encoded_body, $headers, Argument::type('int'))
            ->shouldBeCalled()
            ->willReturn(new RawResponse($headers, $encoded_body, 200));

        $link_id = new LinkID($document_link);
        $response = $this->whatsapp_app_cloud_api->sendAudio(
            $to,
            $link_id
        );

        $this->assertEquals(200, $response->httpStatusCode());
        $this->assertEquals($body, $response->decodedBody());
        $this->assertEquals($encoded_body, $response->body());
        $this->assertEquals(false, $response->isError());
    }

    public function test_send_image_id()
    {
        $to = $this->faker->phoneNumber;
        $url = $this->buildRequestUri();
        $caption = $this->faker->text;
        $document_id = $this->faker->uuid;

        $body = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $to,
            'type' => 'image',
            'image' => [
                'caption' => $caption,
                'id' => $document_id,
            ],
        ];
        $encoded_body = json_encode($body);
        $headers = [
            'Authorization' => 'Bearer ' . WhatsAppCloudApiTestConfiguration::$access_token,
            'Content-Type' => 'application/json',
        ];

        $this->client_handler
            ->send($url, $encoded_body, $headers, Argument::type('int'))
            ->shouldBeCalled()
            ->willReturn(new RawResponse($headers, $encoded_body, 200));

        $media_id = new MediaObjectID($document_id);
        $response = $this->whatsapp_app_cloud_api->sendImage(
            $to,
            $media_id,
            $caption
        );

        $this->assertEquals(200, $response->httpStatusCode());
        $this->assertEquals($body, $response->decodedBody());
        $this->assertEquals($encoded_body, $response->body());
        $this->assertEquals(false, $response->isError());
    }

    public function test_send_image_link()
    {
        $to = $this->faker->phoneNumber;
        $url = $this->buildRequestUri();
        $caption = $this->faker->text;
        $document_link = $this->faker->url;

        $body = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $to,
            'type' => 'image',
            'image' => [
                'caption' => $caption,
                'link' => $document_link,
            ],
        ];
        $encoded_body = json_encode($body);
        $headers = [
            'Authorization' => 'Bearer ' . WhatsAppCloudApiTestConfiguration::$access_token,
            'Content-Type' => 'application/json',
        ];

        $this->client_handler
            ->send($url, $encoded_body, $headers, Argument::type('int'))
            ->shouldBeCalled()
            ->willReturn(new RawResponse($headers, $encoded_body, 200));

        $link_id = new LinkID($document_link);
        $response = $this->whatsapp_app_cloud_api->sendImage(
            $to,
            $link_id,
            $caption
        );

        $this->assertEquals(200, $response->httpStatusCode());
        $this->assertEquals($body, $response->decodedBody());
        $this->assertEquals($encoded_body, $response->body());
        $this->assertEquals(false, $response->isError());
    }

    private function buildRequestUri(): string
    {
        return Client::BASE_GRAPH_URL . '/' . WhatsAppCloudApiTestConfiguration::$graph_version . '/' . WhatsAppCloudApiTestConfiguration::$from_phone_number_id . '/messages';
    }
}