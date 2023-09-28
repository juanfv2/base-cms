import {BaseCmsModule, JfUtils, JfAuthGuard, JfAuthService, JfMessageService, JfErrorHandlerService} from 'base-cms' // from '@juanfv2/base-cms'

import {APP_BASE_HREF} from '@angular/common'
import {HttpClientModule} from '@angular/common/http'
import {BrowserModule} from '@angular/platform-browser'
import {ServiceWorkerModule} from '@angular/service-worker'
import {NgModule, isDevMode, ErrorHandler} from '@angular/core'

import {AppRoutingModule} from './app-routing.module'
import {AppComponent} from './app.component'
import {AuthModule} from './modules/auth/auth.module'

@NgModule({
  declarations: [AppComponent],
  imports: [
    BrowserModule,
    HttpClientModule,
    AppRoutingModule,
    ServiceWorkerModule.register('ngsw-worker.js', {
      enabled: !isDevMode(),
      // Register the ServiceWorker as soon as the application is stable
      // or after 30 seconds (whichever comes first).
      registrationStrategy: 'registerWhenStable:30000',
    }),

    AuthModule,

    BaseCmsModule,
  ],
  providers: [
    {provide: APP_BASE_HREF, useFactory: JfUtils.getBaseLocation},
    /* catch-errors / // for-build
    {provide: ErrorHandler, useClass: JfErrorHandlerService},
    /* */
    JfAuthGuard,
    JfAuthService,
    JfMessageService,
    JfErrorHandlerService,
  ],
  bootstrap: [AppComponent],
})
export class AppModule {}
