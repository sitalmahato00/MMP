import { useState, useEffect } from 'react'
import { useNavigate, useParams } from 'react-router-dom'
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query'
import { useForm, useFieldArray } from 'react-hook-form'
import { zodResolver } from '@hookform/resolvers/zod'
import { z } from 'zod'
import { materialRequestService } from '@/services/material-request.service'
import { NepaliDatePicker } from '@/components/nepali-date/NepaliDatePicker'
import { t } from '@/i18n'
import { useAuth } from '@/stores/auth'
import { Plus, Trash2, Save, Send, Printer, FileDown, Copy, ArrowLeft } from 'lucide-react'
import type { Department, MaterialRequestItem } from '@/types'
import api from '@/lib/api'
import type { ApiResponse } from '@/types'

const itemSchema = z.object({
  item_name: z.string().min(1, 'Required'),
  specification: z.string().optional(),
  unit: z.string().min(1, 'Required'),
  quantity: z.number().min(1, 'Min 1'),
  remarks: z.string().optional(),
})

const formSchema = z.object({
  date_bs: z.string().min(1, 'Required'),
  department_id: z.number().min(1, 'Required'),
  remarks: z.string().optional(),
  items: z.array(itemSchema).min(1, 'At least one item required'),
})

type FormData = z.infer<typeof formSchema>

export function MaterialRequestForm() {
  const { id } = useParams()
  const isEdit = !!id
  const navigate = useNavigate()
  const queryClient = useQueryClient()
  const { user } = useAuth()
  const [isSubmitted, setIsSubmitted] = useState(false)

  const { data: departments } = useQuery({
    queryKey: ['departments'],
    queryFn: async () => {
      const res = await api.get<ApiResponse<Department[]>>('/departments')
      return res.data.data
    },
  })

  const { data: formData } = useQuery({
    queryKey: ['material-request', id],
    queryFn: () => materialRequestService.getById(Number(id)),
    enabled: isEdit,
  })

  const { register, control, handleSubmit, setValue, watch, formState: { errors }, reset } = useForm<FormData>({
    resolver: zodResolver(formSchema),
    defaultValues: {
      date_bs: '',
      department_id: 0,
      remarks: '',
      items: [{ item_name: '', specification: '', unit: '', quantity: 1, remarks: '' }],
    },
  })

  const { fields, append, remove } = useFieldArray({ control, name: 'items' })

  useEffect(() => {
    if (formData) {
      reset({
        date_bs: formData.date_bs,
        department_id: formData.department_id,
        remarks: formData.remarks || '',
        items: formData.items.map((i: MaterialRequestItem) => ({
          item_name: i.item_name,
          specification: i.specification || '',
          unit: i.unit,
          quantity: i.quantity,
          remarks: i.remarks || '',
        })),
      })
      setIsSubmitted(formData.status !== 'draft')
    }
  }, [formData, reset])

  const mutation = useMutation({
    mutationFn: (data: FormData & { status?: string }) =>
      isEdit
        ? materialRequestService.update(Number(id), data)
        : materialRequestService.create(data),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['material-requests'] })
      navigate('/material-requests')
    },
  })

  const submitMutation = useMutation({
    mutationFn: (data: FormData) =>
      materialRequestService.create({ ...data, status: 'submitted' }),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['material-requests'] })
      navigate('/material-requests')
    },
  })

  const onSubmit = (data: FormData) => mutation.mutate(data)
  const onSubmitSubmit = (data: FormData) => submitMutation.mutate(data)

  const handleDateChange = (v: string) => setValue('date_bs', v)

  if (isEdit && !formData) {
    return <div className="flex h-64 items-center justify-center text-muted">{t('common.loading')}</div>
  }

  return (
    <div className="space-y-6">
      <div className="flex items-center gap-4">
        <button onClick={() => navigate('/material-requests')} className="btn-secondary">
          <ArrowLeft className="h-4 w-4" />
        </button>
        <div>
          <h1 className="page-title">{isEdit ? t('materialRequest.edit') : t('materialRequest.create')}</h1>
        </div>
      </div>

      <form onSubmit={handleSubmit(onSubmit)} className="space-y-6">
        <div className="card">
          <div className="card-header">
            <h2 className="text-lg font-semibold">{t('materialRequest.requestNumber')}</h2>
          </div>
          <div className="card-body grid grid-cols-1 gap-4 md:grid-cols-3">
            <div>
              <label className="form-label">{t('materialRequest.date')}</label>
              <NepaliDatePicker
                value={watch('date_bs')}
                onChange={handleDateChange}
                error={errors.date_bs?.message}
              />
            </div>
            <div>
              <label className="form-label">{t('materialRequest.department')}</label>
              <select {...register('department_id', { valueAsNumber: true })} className="form-input">
                <option value="">Select</option>
                {departments?.map((d: Department) => (
                  <option key={d.id} value={d.id}>{d.name}</option>
                ))}
              </select>
              {errors.department_id && <p className="form-error">{errors.department_id.message}</p>}
            </div>
            <div>
              <label className="form-label">{t('materialRequest.applicant')}</label>
              <input type="text" value={user?.name || ''} className="form-input" disabled />
            </div>
          </div>
        </div>

        <div className="card">
          <div className="card-header flex items-center justify-between">
            <h2 className="text-lg font-semibold">{t('materialRequest.items')}</h2>
            <button
              type="button"
              onClick={() => append({ item_name: '', specification: '', unit: '', quantity: 1, remarks: '' })}
              className="btn-secondary gap-2 text-sm"
            >
              <Plus className="h-4 w-4" />
              {t('materialRequest.addItem')}
            </button>
          </div>
          <div className="card-body">
            <div className="overflow-x-auto">
              <table className="w-full text-sm">
                <thead>
                  <tr className="border-b bg-gray-50">
                    <th className="px-3 py-2 text-left font-medium text-muted">#</th>
                    <th className="px-3 py-2 text-left font-medium text-muted">{t('materialRequest.itemName')}</th>
                    <th className="px-3 py-2 text-left font-medium text-muted">{t('materialRequest.specification')}</th>
                    <th className="px-3 py-2 text-left font-medium text-muted">{t('materialRequest.unit')}</th>
                    <th className="px-3 py-2 text-left font-medium text-muted">{t('materialRequest.quantity')}</th>
                    <th className="px-3 py-2 text-left font-medium text-muted">{t('materialRequest.remarks')}</th>
                    <th className="px-3 py-2 text-left font-medium text-muted"></th>
                  </tr>
                </thead>
                <tbody>
                  {fields.map((field, idx) => (
                    <tr key={field.id} className="border-b">
                      <td className="px-3 py-2 text-muted">{idx + 1}</td>
                      <td className="px-3 py-2">
                        <input {...register(`items.${idx}.item_name`)} className="form-input" />
                      </td>
                      <td className="px-3 py-2">
                        <input {...register(`items.${idx}.specification`)} className="form-input" />
                      </td>
                      <td className="px-3 py-2">
                        <select {...register(`items.${idx}.unit`)} className="form-input">
                          <option value="">Select</option>
                          <option value="pcs">Pcs</option>
                          <option value="kg">Kg</option>
                          <option value="m">Meter</option>
                          <option value="ltr">Liter</option>
                          <option value="box">Box</option>
                          <option value="set">Set</option>
                          <option value="pair">Pair</option>
                          <option value="carton">Carton</option>
                        </select>
                      </td>
                      <td className="px-3 py-2">
                        <input type="number" {...register(`items.${idx}.quantity`, { valueAsNumber: true })} className="form-input w-20" />
                      </td>
                      <td className="px-3 py-2">
                        <input {...register(`items.${idx}.remarks`)} className="form-input" />
                      </td>
                      <td className="px-3 py-2">
                        {fields.length > 1 && (
                          <button type="button" onClick={() => remove(idx)} className="btn-secondary px-2 py-1 text-red-600">
                            <Trash2 className="h-4 w-4" />
                          </button>
                        )}
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <div className="card">
          <div className="card-header">
            <h2 className="text-lg font-semibold">{t('materialRequest.remarks')}</h2>
          </div>
          <div className="card-body">
            <textarea {...register('remarks')} rows={3} className="form-input" />
          </div>
        </div>

        <div className="flex items-center justify-between">
          <button type="button" onClick={() => navigate('/material-requests')} className="btn-secondary">
            {t('common.cancel')}
          </button>
          <div className="flex gap-2">
            {!isSubmitted && (
              <>
                <button type="submit" className="btn-secondary gap-2">
                  <Save className="h-4 w-4" />
                  {t('materialRequest.saveDraft')}
                </button>
                <button
                  type="button"
                  onClick={handleSubmit(onSubmitSubmit)}
                  className="btn-primary gap-2"
                >
                  <Send className="h-4 w-4" />
                  {t('materialRequest.submit')}
                </button>
              </>
            )}
            {isEdit && (
              <>
                <button type="button" className="btn-secondary gap-2">
                  <Printer className="h-4 w-4" />
                  {t('materialRequest.print')}
                </button>
                <button type="button" onClick={() => materialRequestService.downloadPdf(Number(id))} className="btn-secondary gap-2">
                  <FileDown className="h-4 w-4" />
                  {t('materialRequest.downloadPdf')}
                </button>
                <button type="button" onClick={() => materialRequestService.duplicate(Number(id))} className="btn-secondary gap-2">
                  <Copy className="h-4 w-4" />
                  {t('materialRequest.duplicate')}
                </button>
              </>
            )}
          </div>
        </div>
      </form>
    </div>
  )
}
