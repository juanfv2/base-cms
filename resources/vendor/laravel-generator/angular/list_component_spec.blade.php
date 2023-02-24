import {HttpClientTestingModule} from '@angular/common/http/testing'
import {ComponentFixture, TestBed} from '@angular/core/testing'
import {RouterTestingModule} from '@angular/router/testing'
import {FormsModule} from '@angular/forms'
import {BaseCmsModule, JfResponseList} from 'base-cms' // @juanfv2/base-cms
import { {{ $config->modelNames->name }} } from 'src/app/models/_models';
import {DOMHelper, Helpers} from 'src/testing/helpers'

import { {{ $config->modelNames->name }}ListComponent } from './{{ $config->modelNames->dashed }}-list.component';

describe('{{ $config->modelNames->name }}ListComponent', () => {
let component: {{ $config->modelNames->name }}ListComponent;
let domHelper: DOMHelper<{{ $config->modelNames->name }}ListComponent>
let fixture: ComponentFixture<{{ $config->modelNames->name }}ListComponent>;

beforeEach(async () => {
await TestBed.configureTestingModule({
declarations: [{{ $config->modelNames->name }}ListComponent],
imports: [HttpClientTestingModule, RouterTestingModule, FormsModule, BaseCmsModule],
})
.compileComponents();

fixture = TestBed.createComponent({{ $config->modelNames->name }}ListComponent);
component = fixture.componentInstance;
const items = Helpers.generateObjectsMock(component.itemLabels, 3)
component.responseList = {content: items} as JfResponseList<{{ $config->modelNames->name }}>

domHelper = new DOMHelper(fixture)
fixture.detectChanges();
});

it('should create', () => {
expect(component).toBeTruthy()
})

it('should have [3] headers', () => {
expect(domHelper.count('table.table thead th')).toBe(component.fieldsInList.length + 1)
})

it('should render [3] items', () => {
expect(domHelper.count('table.table tbody tr')).toBe(3)
})
});
