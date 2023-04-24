import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule, ReactiveFormsModule } from '@angular/forms';

import { RouterModule } from '@angular/router';
import { NgIdleModule } from '@ng-idle/core';

import { LoginComponent } from './components/login/login.component';
import { AdminComponent } from './components/admin/admin.component';
import { BaseCmsModule } from 'base-cms'; // from '@juanfv2/base-cms'



@NgModule({
  declarations: [
    LoginComponent,
    AdminComponent
  ],
  imports: [
    CommonModule,
    RouterModule,
    FormsModule,
    ReactiveFormsModule,

    NgIdleModule.forRoot(),
    BaseCmsModule

  ],
  exports: [
    LoginComponent,
    AdminComponent
  ]
})
export class AuthModule { }
