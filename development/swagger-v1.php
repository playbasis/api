<?php
/**
 * @SWG\Swagger(
 *     schemes={"https"},
 *     host=API_HOST,
 *     basePath="/",
 *     @SWG\Info(
 *         version="1.0",
 *         title="Playbasis API",
 *         description="API Explorer",
 *         termsOfService="",
 *         @SWG\Contact(
 *             email="info@playbasis.com"
 *         ),
 *     ),
 *     @SWG\SecurityScheme(
 *         securityDefinition="apiKey",
 *         type="apiKey",
 *         in="query",
 *         name="api_key",
 *     ),
 *     @SWG\SecurityScheme(
 *         securityDefinition="token",
 *         type="apiKey",
 *         in="query",
 *         name="token",
 *     ),
 *     security={
 *         {
 *            "apiKey": {},
 *            "token": {}
 *         }
 *     }
 * )
 */
