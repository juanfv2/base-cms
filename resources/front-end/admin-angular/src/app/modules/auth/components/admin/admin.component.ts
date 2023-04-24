import {Component, isDevMode, OnInit} from '@angular/core'
import {Router} from '@angular/router'
import {DEFAULT_INTERRUPTSOURCES, Idle} from '@ng-idle/core'
import {JfAuthService, JfMessageService} from 'base-cms' // from '@juanfv2/base-cms'
import {User} from 'src/app/models/_models'
import {k} from 'src/environments/k'
import {l} from 'src/environments/l'

@Component({
  selector: 'app-admin',
  templateUrl: './admin.component.html',
  styleUrls: ['./admin.component.scss'],
})
export class AdminComponent {
  currentUser!: User
  labels = l

  constructor(
    private idle: Idle,
    public router: Router,
    // private ngZone: NgZone,
    // private keepAlive: Keepalive,
    private authService: JfAuthService,
    private messageService: JfMessageService
  ) {
    this.authService.currentUser.subscribe((u: User) => (this.currentUser = u))

    this.time2live()
    this.reset()
  }

  time2live(): void {
    const timeOut = k.expireTimeOut * 60
    const timeToIdle = k.expireTime * (isDevMode() ? k.timeToLive: k.timeToLiveProd) - timeOut

    // establecer un tiempo de espera inactivo de 5 segundos, con fines de prueba.
    this.idle.setIdle(timeToIdle)
    // establecer un período de tiempo de espera de 5 segundos. después de 10 segundos de inactividad, el usuario se considerará agotado.
    this.idle.setTimeout(timeOut)
    // sets the default interrupts, in this case, things like clicks, scrolls, touches to the document
    this.idle.setInterrupts(DEFAULT_INTERRUPTSOURCES)

    this.idle.onIdleEnd.subscribe(() => {
      // console.log('time: onIdleEnd', time);

      const payload = {notification: {title: k.project_name, body: 'Ya no está inactivo.'}}
      this.messageService.currentMessage.next(payload)
    })
    this.idle.onTimeout.subscribe(() => {
      // console.log('time: onTimeout', time);

      this.authService.logout()
      this.router.navigate(['login'])

      const payload = {notification: {title: k.project_name, body: '¡Desconectado por inactividad!'}}
      this.messageService.currentMessage.next(payload)
      // this.messageService.success(k.project_name, 'Desconectado por inactividad');
    })
    this.idle.onIdleStart.subscribe(() => {
      // console.log('time: onIdleStart', time);

      const payload = {notification: {title: k.project_name, body: '¡Te has quedado inactivo!'}}
      this.messageService.currentMessage.next(payload)
    })
    this.idle.onTimeoutWarning.subscribe((countdown) => {
      // console.log('time: onTimeoutWarning', time);

      const min = Math.ceil(countdown / 60) - 1

      const payload = {
        notification: {title: k.project_name, body: `¡La sesión se cerrará en aprox. ${min}:${countdown}!`},
      }
      this.messageService.currentMessage.next(payload)
    })

    // sets the ping interval to 15 seconds
    // this.keepAlive.interval(15);

    // this.keepAlive.onPing.subscribe(() => {

    //   console.log('time: onPing', time);

    //   const payload = { notification: { title: k.project_name, body: 'the ping interval to 15 seconds', lastPing: new Date() } };
    //   this.messageService.currentMessage.next(payload);
    // });
  }

  reset(): void {
    this.idle.watch()
    // this.idleState = 'Started.';
    // this.timedOut = false;
  }
}
